<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\InventoryItem;
use App\Models\InventoryProcedureTemplate;
use App\Models\InventoryProcedureTemplateItem;
use App\Models\InventoryStockMovement;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitProcedure;
use App\Services\Inventory\InventoryProcedureConsumptionService;
use App\Services\Inventory\InventoryStockMovementService;
use App\Support\Inventory\InventoryMovementType;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_inventory_dashboard_requires_permission(): void
    {
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('inventory.dashboard'))
            ->assertOk()
            ->assertSee(__('inventory.dashboard_title'), false);
    }

    public function test_stock_movement_deducts_quantity(): void
    {
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $this->actingAs($admin);

        $item = InventoryItem::query()->create([
            'name' => 'Gloves',
            'quantity_on_hand' => 10,
            'minimum_quantity' => 2,
            'status' => 'active',
        ]);

        app(InventoryStockMovementService::class)->record(
            $item,
            InventoryMovementType::STOCK_OUT,
            3,
        );

        $item->refresh();
        $this->assertEquals(7.0, (float) $item->quantity_on_hand);
        $this->assertDatabaseHas('inventory_stock_movements', [
            'inventory_item_id' => $item->id,
            'type' => InventoryMovementType::STOCK_OUT,
        ]);
    }

    public function test_procedure_consumption_on_completed_visit(): void
    {
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $this->actingAs($admin);

        $gloves = InventoryItem::query()->create([
            'name' => 'Gloves box',
            'quantity_on_hand' => 5,
            'minimum_quantity' => 1,
            'status' => 'active',
        ]);

        $template = InventoryProcedureTemplate::query()->create([
            'name' => 'Dental Filling',
            'name_normalized' => 'dental filling',
            'auto_consume' => true,
        ]);

        InventoryProcedureTemplateItem::query()->create([
            'inventory_procedure_template_id' => $template->id,
            'inventory_item_id' => $gloves->id,
            'quantity' => 2,
        ]);

        $patient = Patient::query()->create([
            'file_number' => 'INV-'.uniqid(),
            'full_name' => 'Test Patient',
            'status' => 'active',
        ]);
        $doctor = Doctor::query()->create([
            'full_name' => 'Dr Test',
            'status' => 'active',
        ]);
        $visit = Visit::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'visit_date' => now()->toDateString(),
            'status' => Visit::STATUS_COMPLETED,
        ]);
        VisitProcedure::query()->create([
            'visit_id' => $visit->id,
            'name' => 'Dental Filling',
        ]);

        $movements = app(InventoryProcedureConsumptionService::class)->consumeForCompletedVisit($visit);

        $this->assertCount(1, $movements);
        $gloves->refresh();
        $this->assertEquals(3.0, (float) $gloves->quantity_on_hand);
        $this->assertTrue(
            InventoryStockMovement::query()
                ->where('visit_id', $visit->id)
                ->where('type', InventoryMovementType::CONSUMPTION)
                ->exists()
        );
    }

    public function test_reports_page_shows_inventory_valuation(): void
    {
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee(__('reports.kpi_inventory_valuation'), false)
            ->assertSee(__('reports.section_inventory'), false);
    }

    public function test_purchase_receive_can_post_linked_expense(): void
    {
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $this->actingAs($admin);

        $item = InventoryItem::query()->create([
            'name' => 'Syringes',
            'quantity_on_hand' => 0,
            'minimum_quantity' => 0,
            'status' => 'active',
            'unit_cost' => 5,
        ]);

        $this->post(route('inventory.purchases.store'), [
            'purchase_date' => now()->toDateString(),
            'post_to_expense' => '1',
            'lines' => [
                ['inventory_item_id' => $item->id, 'quantity' => 10, 'unit_cost' => 5],
            ],
        ])->assertRedirect(route('inventory.purchases.index'));

        $this->assertDatabaseHas('inventory_purchases', [
            'total_amount' => 50,
        ]);
        $this->assertDatabaseHas('expenses', [
            'amount' => 50,
        ]);
    }

    public function test_tenant_cannot_access_other_clinic_item(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $clinicB = Clinic::query()->create(['name' => 'Clinic B', 'slug' => 'clinic-b-'.uniqid()]);
        $itemB = InventoryItem::withoutGlobalScopes()->create([
            'clinic_id' => $clinicB->id,
            'name' => 'Secret item',
            'quantity_on_hand' => 1,
            'status' => 'active',
        ]);

        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('inventory.items.edit', ['item' => $itemB->id]))
            ->assertNotFound();
    }
}
