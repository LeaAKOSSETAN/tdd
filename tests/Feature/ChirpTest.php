<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Chirp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChirpTest extends TestCase
{
    /**
     * A basic feature test example.
     */
     use RefreshDatabase;

    public function test_un_utilisateur_peut_creer_un_chirp(): void
    {
        $utilisateur = User::factory()->create();
        $this->actingAs($utilisateur);

        $response= $this->post('/chirps', [
            'content' => 'Mon premier chirp!'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('chirps', [
            'content' => 'Mon premier chirp !',
            'user_id' => $utilisateur->id,
        ]);
    }

    public function test_un_chirp_ne_peut_pas_avoir_un_contenu_vide()
    {
        $utilisateur = User::factory()->create();
        $this->actingAs($utilisateur);

        $reponse = $this->post('/chirps', [
        'content' => ''
        ]);

        $reponse->assertSessionHasErrors(['contenu']);
    }

    public function test_un_chirp_ne_peut_pas_depasse_255_caracteres()
    {
        $utilisateur = User::factory()->create();
        $this->actingAs($utilisateur);

        $reponse = $this->post('/chirps', [
        'content' => str_repeat('a', 256)
        ]);

        $reponse->assertSessionHasErrors(['contenu']);
    }

    public function test_les_chirps_sont_affiches_sur_la_page_d_accueil()
    {
        $chirps = Chirp::factory()->count(3)->create();
        $reponse = $this->get('/');
        foreach ($chirps as $chirp) {
        $reponse->assertSee($chirp->contenu);
        }
    }

    public function test_un_utilisateur_peut_modifier_son_chirp()
    {
        $utilisateur = User::factory()->create();
        $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);
        $this->actingAs($utilisateur);

        $reponse = $this->put("/chirps/{$chirp->id}", [
        'content' => 'Chirp modifié'
        ]);
    
        $reponse->assertStatus(200);

        // Vérifie si le chirp existe dans la base de donnée.
        $this->assertDatabaseHas('chirps', [
        'id' => $chirp->id,
        'content' => 'Chirp modifié',
        ]);
    }

    public function test_un_utilisateur_peut_supprimer_son_chirp()
    {
        $utilisateur = User::factory()->create();
        $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);
        $this->actingAs($utilisateur);
        
        $reponse = $this->delete("/chirps/{$chirp->id}");
        $reponse->assertStatus(200);
    
        $this->assertDatabaseMissing('chirps', [
        'id' => $chirp->id,
        ]);
    }



}
