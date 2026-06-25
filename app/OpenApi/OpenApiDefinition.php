<?php

namespace App\OpenApi;

/**
 * @OA\Info(
 *     title="Dinastia ERP API",
 *     version="1.0.0",
 *     description="API REST para el sistema de gestión de nómina y recursos humanos Dinastia ERP",
 *     @OA\Contact(email="soporte@dinastia.co")
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="token",
 *     description="Sanctum token. Obtener via POST /api/v1/auth/login"
 * )
 *
 * @OA\Server(url="/api/v1", description="API v1")
 *
 * @OA\Parameter(
 *     name="X-Empresa-ID",
 *     in="header",
 *     required=true,
 *     parameter="EmpresaHeader",
 *     description="ID de la empresa activa para el contexto multi-tenant",
 *     @OA\Schema(type="integer", example=1)
 * )
 */
class OpenApiDefinition
{
}
