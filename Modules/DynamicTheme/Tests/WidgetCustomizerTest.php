<?php

namespace Modules\DynamicTheme\Tests;

use Tests\TestCase;
use Modules\DynamicTheme\Entities\WidgetCustomizer;
use Modules\DynamicTheme\Entities\ColorPreset;
use Modules\DynamicTheme\Entities\WidgetDesignTemplate;
use Modules\DynamicTheme\Entities\ConfigWidgetOverride;
use Modules\DynamicTheme\Entities\ClientConfiguration;
use Modules\DynamicTheme\Services\CustomizerService;

class WidgetCustomizerTest extends TestCase
{
    protected $configWidgetOverride;
    protected $configuration;

    protected function setUp(): void
    {
        parent::setUp();

        // إنشاء بيانات اختبار
        $this->configuration = ClientConfiguration::factory()->create();
        $this->configWidgetOverride = ConfigWidgetOverride::factory()
            ->create(['configuration_id' => $this->configuration->id]);
    }

    /** @test */
    public function can_create_widget_customizer()
    {
        $designConfig = [
            'shape_config' => ['borderRadius' => 8],
            'color_config' => [
                'primary' => '#3b82f6',
                'secondary' => '#10b981',
            ],
        ];

        $customizer = CustomizerService::createCustomizer(
            $this->configWidgetOverride->id,
            $designConfig,
            'Test Customizer'
        );

        $this->assertInstanceOf(WidgetCustomizer::class, $customizer);
        $this->assertEquals('Test Customizer', $customizer->name);
        $this->assertEquals(8, $customizer->shape_config['borderRadius']);
    }

    /** @test */
    public function can_generate_css()
    {
        $customizer = WidgetCustomizer::factory()
            ->create([
                'config_widget_override_id' => $this->configWidgetOverride->id,
                'shape_config' => ['borderRadius' => 8],
                'color_config' => ['primary' => '#3b82f6'],
            ]);

        $css = $customizer->generateCSS();

        $this->assertStringContainsString('border-radius: 8px', $css);
    }

    /** @test */
    public function can_create_color_preset()
    {
        $preset = ColorPreset::create([
            'configuration_id' => $this->configuration->id,
            'name' => 'Test Preset',
            'colors' => [
                'primary' => '#3b82f6',
                'secondary' => '#10b981',
                'accent' => '#f59e0b',
            ],
        ]);

        $this->assertInstanceOf(ColorPreset::class, $preset);
        $this->assertEquals('#3b82f6', $preset->getPrimaryColor());
    }

    /** @test */
    public function can_create_design_template()
    {
        $template = WidgetDesignTemplate::create([
            'configuration_id' => $this->configuration->id,
            'name' => 'Blue Theme',
            'design_config' => [
                'color_config' => ['primary' => '#3b82f6'],
                'shape_config' => ['borderRadius' => 8],
            ],
        ]);

        $this->assertInstanceOf(WidgetDesignTemplate::class, $template);
        $this->assertEquals('Blue Theme', $template->name);
    }

    /** @test */
    public function can_apply_color_preset()
    {
        $customizer = WidgetCustomizer::factory()
            ->create(['config_widget_override_id' => $this->configWidgetOverride->id]);

        $preset = ColorPreset::create([
            'configuration_id' => $this->configuration->id,
            'name' => 'Test Preset',
            'colors' => ['primary' => '#ff0000', 'secondary' => '#00ff00'],
        ]);

        $updated = CustomizerService::applyColorPreset($customizer->id, $preset->id);

        $this->assertEquals('#ff0000', $updated->color_config['primary']);
    }

    /** @test */
    public function can_export_design()
    {
        $customizer = WidgetCustomizer::factory()
            ->create([
                'config_widget_override_id' => $this->configWidgetOverride->id,
                'name' => 'Test Design',
                'color_config' => ['primary' => '#3b82f6'],
            ]);

        $exported = CustomizerService::exportDesign($customizer->id);

        $this->assertEquals('Test Design', $exported['name']);
        $this->assertArrayHasKey('design_config', $exported);
    }

    /** @test */
    public function can_import_design()
    {
        $designData = [
            'name' => 'Imported Design',
            'description' => 'Test',
            'design_config' => [
                'color_config' => ['primary' => '#3b82f6'],
            ],
        ];

        $imported = CustomizerService::importDesign(
            $this->configWidgetOverride->id,
            $designData
        );

        $this->assertInstanceOf(WidgetCustomizer::class, $imported);
        $this->assertEquals('Imported Design', $imported->name);
    }

    /** @test */
    public function can_generate_css_variables()
    {
        $designConfig = [
            'color_config' => [
                'primary' => '#3b82f6',
                'secondary' => '#10b981',
            ],
        ];

        $cssVars = CustomizerService::configToCSSVariables($designConfig);

        $this->assertStringContainsString('--color-primary', $cssVars);
        $this->assertStringContainsString('#3b82f6', $cssVars);
    }

    /** @test */
    public function api_endpoint_returns_customizers()
    {
        WidgetCustomizer::factory(3)->create([
            'config_widget_override_id' => $this->configWidgetOverride->id,
        ]);

        $response = $this->actingAs($this->user())
            ->getJson('/api/customizers/widget-override/' . $this->configWidgetOverride->id);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(3, 'customizers');
    }

    /** @test */
    public function api_endpoint_can_create_customizer()
    {
        $data = [
            'config_widget_override_id' => $this->configWidgetOverride->id,
            'shape_config' => ['borderRadius' => 12],
            'color_config' => ['primary' => '#3b82f6'],
            'name' => 'API Test',
        ];

        $response = $this->actingAs($this->user())
            ->postJson('/api/customizers', $data);

        $response->assertCreated()
            ->assertJsonPath('status', 'success');
    }
}
