<?php

namespace Modules\DynamicTheme\Tests\Feature;

use Tests\TestCase;
use Modules\DynamicTheme\Entities\ChildCustomizer;
use Modules\DynamicTheme\Entities\ThemeChild;
use Modules\DynamicTheme\Services\CustomizerService;

class ChildCustomizerTest extends TestCase
{
    protected ThemeChild $themeChild;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->themeChild = ThemeChild::factory()->create();
    }

    /**
     * Test creating a child customizer
     */
    public function test_create_child_customizer()
    {
        $designConfig = [
            'shape_config' => ['borderRadius' => 5],
            'color_config' => ['background' => '#ffffff'],
        ];

        $child = CustomizerService::createChildCustomizer(
            $this->themeChild->id,
            $designConfig,
            name: 'Test Child'
        );

        $this->assertInstanceOf(ChildCustomizer::class, $child);
        $this->assertEquals('Test Child', $child->name);
        $this->assertEquals(5, $child->shape_config['borderRadius']);
    }

    /**
     * Test updating child design
     */
    public function test_update_child_design()
    {
        $child = ChildCustomizer::factory()->create([
            'theme_child_id' => $this->themeChild->id,
            'shape_config' => ['borderRadius' => 0],
        ]);

        $updated = CustomizerService::updateChildDesign($child->id, [
            'shape_config' => ['borderRadius' => 10],
        ]);

        $this->assertEquals(10, $updated->shape_config['borderRadius']);
    }

    /**
     * Test generating CSS
     */
    public function test_generate_css()
    {
        $child = ChildCustomizer::factory()->create([
            'shape_config' => ['borderRadius' => 5],
            'color_config' => ['background' => '#ffffff'],
        ]);

        $css = CustomizerService::generateChildCSS($child->id);

        $this->assertStringContainsString('border-radius', $css);
        $this->assertStringContainsString('#ffffff', $css);
    }

    /**
     * Test cloning child customizer
     */
    public function test_clone_child_customizer()
    {
        $original = ChildCustomizer::factory()->create([
            'name' => 'Original',
            'theme_child_id' => $this->themeChild->id,
        ]);

        $clone = CustomizerService::cloneChildCustomizer($original->id);

        $this->assertNotEquals($original->id, $clone->id);
        $this->assertEquals($original->shape_config, $clone->shape_config);
        $this->assertStringContainsString('Copy', $clone->name);
    }

    /**
     * Test exporting child design
     */
    public function test_export_child_design()
    {
        $child = ChildCustomizer::factory()->create([
            'name' => 'Test Child',
            'description' => 'Test Description',
            'shape_config' => ['borderRadius' => 5],
        ]);

        $export = CustomizerService::exportChildDesign($child->id);

        $this->assertEquals('Test Child', $export['name']);
        $this->assertEquals('Test Description', $export['description']);
        $this->assertArrayHasKey('design_config', $export);
    }

    /**
     * Test importing child design
     */
    public function test_import_child_design()
    {
        $designData = [
            'name' => 'Imported Child',
            'description' => 'Imported Design',
            'design_config' => [
                'shape_config' => ['borderRadius' => 8],
                'color_config' => ['background' => '#f5f5f5'],
            ],
        ];

        $imported = CustomizerService::importChildDesign(
            $this->themeChild->id,
            $designData
        );

        $this->assertEquals('Imported Child', $imported->name);
        $this->assertEquals(8, $imported->shape_config['borderRadius']);
    }

    /**
     * Test batch updating children order
     */
    public function test_batch_update_children_order()
    {
        $child1 = ChildCustomizer::factory()->create(['theme_child_id' => $this->themeChild->id]);
        $child2 = ChildCustomizer::factory()->create(['theme_child_id' => $this->themeChild->id]);

        CustomizerService::batchUpdateChildrenOrder([
            ['id' => $child1->id, 'order' => 1],
            ['id' => $child2->id, 'order' => 0],
        ]);

        $this->assertEquals(1, $child1->fresh()->display_order);
        $this->assertEquals(0, $child2->fresh()->display_order);
    }

    /**
     * Test API create endpoint
     */
    public function test_api_create_child_customizer()
    {
        $data = [
            'theme_child_id' => $this->themeChild->id,
            'name' => 'API Test Child',
            'shape_config' => ['borderRadius' => 5],
            'color_config' => ['background' => '#ffffff'],
        ];

        $response = $this->postJson('/api/child-customizers', $data);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'API Test Child');
    }

    /**
     * Test API update endpoint
     */
    public function test_api_update_child_customizer()
    {
        $child = ChildCustomizer::factory()->create([
            'name' => 'Original Name',
            'theme_child_id' => $this->themeChild->id,
        ]);

        $response = $this->putJson("/api/child-customizers/{$child->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Name');
    }

    /**
     * Test API delete endpoint
     */
    public function test_api_delete_child_customizer()
    {
        $child = ChildCustomizer::factory()->create([
            'theme_child_id' => $this->themeChild->id,
        ]);

        $response = $this->deleteJson("/api/child-customizers/{$child->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted($child);
    }

    /**
     * Test API generate CSS endpoint
     */
    public function test_api_generate_css()
    {
        $child = ChildCustomizer::factory()->create([
            'shape_config' => ['borderRadius' => 5],
            'color_config' => ['background' => '#ffffff'],
        ]);

        $response = $this->getJson("/api/child-customizers/{$child->id}/generate-css");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.css', fn ($css) => str_contains($css, 'border-radius'));
    }

    /**
     * Test child visibility toggle
     */
    public function test_child_visibility_toggle()
    {
        $child = ChildCustomizer::factory()->create([
            'is_visible' => true,
            'theme_child_id' => $this->themeChild->id,
        ]);

        $this->assertTrue($child->is_visible);
        $this->assertEquals('visible', $child->getVisibilityStatus());

        $child->update(['is_visible' => false]);

        $this->assertFalse($child->fresh()->is_visible);
        $this->assertEquals('hidden', $child->fresh()->getVisibilityStatus());
    }

    /**
     * Test child relationships
     */
    public function test_child_relationships()
    {
        $child = ChildCustomizer::factory()->create([
            'theme_child_id' => $this->themeChild->id,
        ]);

        $this->assertInstanceOf(ThemeChild::class, $child->themeChild);
        $this->assertEquals($this->themeChild->id, $child->themeChild->id);
    }

    /**
     * Test validation for required fields
     */
    public function test_validation_required_fields()
    {
        $response = $this->postJson('/api/child-customizers', []);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('errors.theme_child_id', fn ($val) => is_array($val));
    }

    /**
     * Test color config generation
     */
    public function test_color_config_generation()
    {
        $child = ChildCustomizer::factory()->create([
            'color_config' => [
                'background' => '#ff0000',
                'text' => '#ffffff',
            ],
        ]);

        $css = $child->generateCSS();

        $this->assertStringContainsString('#ff0000', $css);
        $this->assertStringContainsString('#ffffff', $css);
    }

    /**
     * Test border config generation
     */
    public function test_border_config_generation()
    {
        $child = ChildCustomizer::factory()->create([
            'border_config' => [
                'width' => 2,
                'style' => 'dashed',
                'color' => '#000000',
            ],
        ]);

        $css = $child->generateCSS();

        $this->assertStringContainsString('border', $css);
        $this->assertStringContainsString('2px', $css);
    }

    /**
     * Test shadow config generation
     */
    public function test_shadow_config_generation()
    {
        $child = ChildCustomizer::factory()->create([
            'shadow_config' => [
                'offsetX' => 2,
                'offsetY' => 4,
                'blur' => 6,
                'opacity' => 0.3,
            ],
        ]);

        $css = $child->generateCSS();

        $this->assertStringContainsString('box-shadow', $css);
        $this->assertStringContainsString('2px', $css);
        $this->assertStringContainsString('4px', $css);
    }
}
