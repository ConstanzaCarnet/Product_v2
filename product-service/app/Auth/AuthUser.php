<?php
namespace App\Auth;
//el ProductService no debe conocer User, solo id y role(esto seria una simulación de un Auth aparte)
//AuthUser es un DTO de seguridad, no un modelo.
class AuthUser
{
    public function __construct(
        public readonly int $id,
        public readonly string $role
    ) {}
}
