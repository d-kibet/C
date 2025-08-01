<?php

namespace App\Console\Commands;

use App\Models\Carpet;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TestAuditSystem extends Command
{
    protected $signature = 'audit:test';
    protected $description = 'Test the audit system functionality';

    public function handle()
    {
        $this->info('Testing Audit System...');
        
        // Get a user to simulate login
        $user = User::first();
        if (!$user) {
            $this->error('No users found. Please create a user first.');
            return;
        }
        
        // Simulate login
        Auth::login($user);
        $this->info("Logged in as: {$user->name}");
        
        // Test creating a carpet record
        $carpet = Carpet::create([
            'uniqueid' => 'TEST-' . time(),
            'size' => 'Medium',
            'price' => '1500',
            'phone' => '0712345678',
            'location' => 'Test Location',
            'date_received' => now(),
            'payment_status' => 'Not Paid',
            'delivered' => 'Not Delivered',
        ]);
        
        $this->info("Created carpet: {$carpet->uniqueid}");
        
        // Test updating the carpet
        $carpet->update([
            'payment_status' => 'Paid',
            'delivered' => 'Delivered',
            'date_delivered' => now(),
        ]);
        
        $this->info("Updated carpet payment status and delivery status");
        
        // Test custom audit event
        $carpet->logAuditEvent('status_changed', [
            'old_status' => 'pending',
            'new_status' => 'completed'
        ]);
        
        $this->info("Logged custom audit event");
        
        // Show audit count
        $auditCount = $carpet->auditTrails()->count();
        $this->info("Total audit records for this carpet: {$auditCount}");
        
        // Clean up test data
        $carpet->delete();
        $this->info("Cleaned up test carpet record");
        
        $this->info('Audit system test completed successfully!');
    }
}