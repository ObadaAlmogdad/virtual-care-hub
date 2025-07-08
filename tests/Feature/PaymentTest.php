<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Payment;
use App\Models\Consultation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function test_can_create_payment_intent()
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $doctor = User::factory()->create([
            'role' => 'doctor',
            'stripe_account_id' => 'acct_test123',
            'stripe_account_verified' => true
        ]);

        $response = $this->actingAs($patient)->postJson('/api/payment/create-intent', [
            'amount' => 100,
            'doctor_id' => $doctor->id,
            'doctor_stripe_account_id' => $doctor->stripe_account_id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'client_secret',
                'payment_id'
            ]);

        $this->assertDatabaseHas('payments', [
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'amount' => 100,
            'fee' => 5,
            'net_amount' => 95,
            'status' => 'pending'
        ]);
    }

    public function test_can_process_refund()
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $doctor = User::factory()->create(['role' => 'doctor']);
        
        $payment = Payment::factory()->create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => 'succeeded',
            'is_refunded' => false
        ]);

        $response = $this->actingAs($patient)->postJson("/api/refund/{$payment->id}", [
            'reason' => 'Doctor was not available'
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Refund processed successfully']);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'is_refunded' => true,
            'refund_reason' => 'Doctor was not available'
        ]);
    }

    public function test_admin_can_refund_any_payment()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $patient = User::factory()->create(['role' => 'patient']);
        $doctor = User::factory()->create(['role' => 'doctor']);
        
        $payment = Payment::factory()->create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => 'succeeded',
            'is_refunded' => false
        ]);

        $response = $this->actingAs($admin)->postJson("/api/refund/{$payment->id}", [
            'reason' => 'System initiated refund'
        ]);

        $response->assertStatus(200);
    }

    public function test_cannot_refund_already_refunded_payment()
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $doctor = User::factory()->create(['role' => 'doctor']);
        
        $payment = Payment::factory()->create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => 'succeeded',
            'is_refunded' => true
        ]);

        $response = $this->actingAs($patient)->postJson("/api/refund/{$payment->id}", [
            'reason' => 'Test refund'
        ]);

        $response->assertStatus(422);
    }

    public function test_can_get_payment_status()
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $doctor = User::factory()->create(['role' => 'doctor']);
        
        $payment = Payment::factory()->create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => 'succeeded'
        ]);

        $response = $this->actingAs($patient)->getJson("/api/payment/{$payment->id}/status");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'is_refunded',
                'refunded_at'
            ]);
    }
} 