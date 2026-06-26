<?php

namespace Tests\Feature\Semana20;

use App\Domains\Shared\Services\SecurityAuditService;
use App\Domains\Shared\Services\PermissionValidator;
use App\Domains\Shared\Services\BackupService;
use App\Models\User;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionServicesTest extends TestCase
{
    use RefreshDatabase;

    protected SecurityAuditService $securityService;
    protected PermissionValidator $permissionService;
    protected BackupService $backupService;

    public function setUp(): void
    {
        parent::setUp();

        $this->securityService = app(SecurityAuditService::class);
        $this->permissionService = app(PermissionValidator::class);
        $this->backupService = app(BackupService::class);
    }

    /** @test */
    public function security_audit_returns_owasp_top_10_checks()
    {
        $resultado = $this->securityService->runFullAudit();

        $this->assertArrayHasKey('owasp_top_10', $resultado);
        $this->assertArrayHasKey('summary', $resultado);
        $this->assertArrayHasKey('a01_broken_access_control', $resultado['owasp_top_10']);
        $this->assertArrayHasKey('a02_cryptographic_failures', $resultado['owasp_top_10']);
        $this->assertArrayHasKey('a03_injection', $resultado['owasp_top_10']);
    }

    /** @test */
    public function security_audit_checks_access_control()
    {
        $resultado = $this->securityService->runFullAudit();
        $accessControl = $resultado['owasp_top_10']['a01_broken_access_control'];

        $this->assertArrayHasKey('multi_tenant_isolation', $accessControl);
        $this->assertArrayHasKey('role_based_access', $accessControl);
        $this->assertArrayHasKey('cross_tenant_prevention', $accessControl);
    }

    /** @test */
    public function security_audit_checks_cryptography()
    {
        $resultado = $this->securityService->runFullAudit();
        $crypto = $resultado['owasp_top_10']['a02_cryptographic_failures'];

        $this->assertArrayHasKey('password_hashing', $crypto);
        $this->assertArrayHasKey('https_enforcement', $crypto);
        $this->assertArrayHasKey('database_encryption', $crypto);

        $this->assertEquals('pass', $crypto['password_hashing']['status']);
    }

    /** @test */
    public function security_audit_checks_injection()
    {
        $resultado = $this->securityService->runFullAudit();
        $injection = $resultado['owasp_top_10']['a03_injection'];

        $this->assertArrayHasKey('sql_injection', $injection);
        $this->assertArrayHasKey('xss_prevention', $injection);
        $this->assertArrayHasKey('command_injection', $injection);

        $this->assertEquals('pass', $injection['sql_injection']['status']);
        $this->assertEquals('pass', $injection['xss_prevention']['status']);
    }

    /** @test */
    public function security_audit_summary_is_complete()
    {
        $resultado = $this->securityService->runFullAudit();
        $summary = $resultado['summary'];

        $this->assertEquals(10, $summary['total_checks']);
        $this->assertIsInt($summary['passed']);
        $this->assertIsInt($summary['warning']);
        $this->assertIsInt($summary['critical']);
        $this->assertNotNull($summary['timestamp']);
    }

    /** @test */
    public function permission_validator_validates_permission_matrix()
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $resultado = $this->permissionValidator->validatePermissionMatrix();

        $this->assertArrayHasKey('timestamp', $resultado);
        $this->assertArrayHasKey('validations', $resultado);
        $this->assertArrayHasKey('all_passed', $resultado);
    }

    /** @test */
    public function permission_validator_checks_multi_tenant_isolation()
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $resultado = $this->permissionValidator->validatePermissionMatrix();
        $validation = $resultado['validations']['multi_tenant_isolation'];

        $this->assertArrayHasKey('status', $validation);
        $this->assertArrayHasKey('total_checks', $validation);
        $this->assertArrayHasKey('details', $validation);
    }

    /** @test */
    public function permission_validator_checks_role_module_access()
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $resultado = $this->permissionValidator->validatePermissionMatrix();
        $validation = $resultado['validations']['role_module_access'];

        $this->assertArrayHasKey('status', $validation);
        $this->assertArrayHasKey('total_usuarios', $validation);
    }

    /** @test */
    public function permission_validator_checks_data_access_control()
    {
        $resultado = $this->permissionValidator->validatePermissionMatrix();
        $validation = $resultado['validations']['data_access_control'];

        $this->assertEquals('pass', $validation['status']);
        $this->assertArrayHasKey('journal_entries', $validation);
        $this->assertArrayHasKey('invoices', $validation);
        $this->assertArrayHasKey('payroll', $validation);
        $this->assertArrayHasKey('audit_logs', $validation);
    }

    /** @test */
    public function backup_service_lists_backups()
    {
        $resultado = $this->backupService->listBackups();

        $this->assertArrayHasKey('total', $resultado);
        $this->assertArrayHasKey('backups', $resultado);
        $this->assertIsInt($resultado['total']);
        $this->assertIsArray($resultado['backups']);
    }

    /** @test */
    public function backup_service_returns_disaster_recovery_plan()
    {
        $plan = $this->backupService->disasterRecoveryPlan();

        $this->assertArrayHasKey('rto', $plan);
        $this->assertArrayHasKey('rpo', $plan);
        $this->assertArrayHasKey('backup_frequency', $plan);
        $this->assertArrayHasKey('retention', $plan);
        $this->assertArrayHasKey('steps', $plan);
        $this->assertArrayHasKey('contacts', $plan);

        $this->assertEquals('1 hora', $plan['rto']);
        $this->assertEquals('15 minutos', $plan['rpo']);
        $this->assertCount(5, $plan['steps']);
    }

    /** @test */
    public function production_controller_health_check_returns_json()
    {
        $response = $this->actingAs(User::factory()->create())
            ->getJson('/api/v1/production/health');

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'checks' => [
                'database',
                'cache',
                'queue',
                'storage',
                'api',
            ],
        ]);
    }

    /** @test */
    public function production_controller_security_audit_endpoint()
    {
        $response = $this->actingAs(User::factory()->create())
            ->getJson('/api/v1/production/security-audit');

        $response->assertOk();
        $response->assertJsonStructure([
            'timestamp',
            'environment',
            'audit',
            'status',
        ]);
    }

    /** @test */
    public function production_controller_deployment_status()
    {
        $response = $this->actingAs(User::factory()->create())
            ->getJson('/api/v1/production/deployment');

        $response->assertOk();
        $response->assertJsonStructure([
            'version',
            'php_version',
            'deployed_at',
            'environment',
            'features',
            'status',
        ]);

        $response->assertJsonPath('status', 'production_ready');
    }

    /** @test */
    public function production_controller_dr_plan_endpoint()
    {
        $response = $this->actingAs(User::factory()->create())
            ->getJson('/api/v1/production/dr-plan');

        $response->assertOk();
        $response->assertJsonStructure([
            'rto',
            'rpo',
            'backup_frequency',
            'retention',
            'steps',
            'contacts',
        ]);
    }
}
