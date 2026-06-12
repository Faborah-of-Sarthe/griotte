<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\Concerns\CreatesGriotteData;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use CreatesGriotteData;
    use RefreshDatabase;

    public function test_update_refuse_un_mot_de_passe_courant_incorrect(): void
    {
        $user = $this->authentifier();

        $this->putJson('/api/user', [
            'email' => 'nouveau@example.test',
            'password' => 'mauvais-mot-de-passe',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Le mot de passe fourni est incorrect.');

        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_update_modifie_l_email_avec_le_mot_de_passe_courant(): void
    {
        $user = $this->authentifier();

        $this->putJson('/api/user', [
            'email' => 'nouveau@example.test',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Votre compte a bien été mis à jour.');

        $this->assertSame('nouveau@example.test', $user->fresh()->email);
    }

    public function test_update_modifie_le_mot_de_passe_si_new_password_est_fourni(): void
    {
        $user = $this->authentifier();

        $this->putJson('/api/user', [
            'email' => $user->email,
            'password' => 'password',
            'new_password' => 'nouveau-secret',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Votre compte a bien été mis à jour.');

        $this->assertTrue(Hash::check('nouveau-secret', $user->fresh()->password));
    }
}
