<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RenderProductOptionsHtmlTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function makeOptionData(float $affectPrice, int $affectType = 0): array
    {
        return [
            'optionCartValue' => [
                1 => [
                    [
                        'option_value' => 'Size XL',
                        'affect_price' => $affectPrice,
                        'affect_type' => $affectType,
                        'option_type' => 'select',
                    ],
                ],
            ],
            'optionInfo' => [
                1 => 'Size',
            ],
        ];
    }

    protected function makeMultiOptionData(array $options): array
    {
        $optionCartValue = [];
        $optionInfo = [];

        foreach ($options as $index => $option) {
            $key = $index + 1;
            $optionCartValue[$key] = [
                [
                    'option_value' => $option['value'],
                    'affect_price' => $option['affect_price'],
                    'affect_type' => $option['affect_type'] ?? 0,
                    'option_type' => $option['option_type'] ?? 'select',
                ],
            ];
            $optionInfo[$key] = $option['name'];
        }

        return [
            'optionCartValue' => $optionCartValue,
            'optionInfo' => $optionInfo,
        ];
    }

    public function test_fixed_option_displays_correct_surcharge(): void
    {
        $options = $this->makeOptionData(15.00, 0);

        $html = render_product_options_html($options, 100.00);

        $this->assertStringContainsString(format_price(15.00), $html);
    }

    public function test_percentage_option_displays_correct_surcharge_with_base_price(): void
    {
        $options = $this->makeOptionData(10, 1);

        $html = render_product_options_html($options, 100.00);

        // 10% of $100 = $10
        $this->assertStringContainsString(format_price(10.00), $html);
    }

    public function test_percentage_option_displays_wrong_surcharge_with_inflated_price(): void
    {
        $options = $this->makeOptionData(10, 1);

        // Simulating the bug: passing $110 (base $100 + $10 option surcharge) as base price
        $htmlWrong = render_product_options_html($options, 110.00);

        // 10% of $110 = $11 (incorrect)
        $this->assertStringContainsString(format_price(11.00), $htmlWrong);

        // Now with correct base price
        $htmlCorrect = render_product_options_html($options, 100.00);

        // 10% of $100 = $10 (correct)
        $this->assertStringContainsString(format_price(10.00), $htmlCorrect);

        // The two outputs should differ — proving wrong base price causes wrong display
        $this->assertNotEquals($htmlWrong, $htmlCorrect);
    }

    public function test_displays_base_price_label(): void
    {
        $options = $this->makeOptionData(5.00, 0);

        $html = render_product_options_html($options, 100.00, displayBasePrice: true);

        $this->assertStringContainsString(format_price(100.00), $html);
    }

    public function test_hides_base_price_label_when_disabled(): void
    {
        $options = $this->makeOptionData(5.00, 0);

        $html = render_product_options_html($options, 100.00, displayBasePrice: false);

        // The surcharge should still show
        $this->assertStringContainsString(format_price(5.00), $html);
    }

    public function test_multiple_fixed_options_display_correct_surcharges(): void
    {
        $options = $this->makeMultiOptionData([
            ['name' => 'Size', 'value' => 'XL', 'affect_price' => 5.00, 'affect_type' => 0],
            ['name' => 'Color', 'value' => 'Gold', 'affect_price' => 3.00, 'affect_type' => 0],
        ]);

        $html = render_product_options_html($options, 100.00);

        $this->assertStringContainsString(format_price(5.00), $html);
        $this->assertStringContainsString(format_price(3.00), $html);
    }

    public function test_mixed_fixed_and_percentage_options_display_correctly(): void
    {
        $options = $this->makeMultiOptionData([
            ['name' => 'Size', 'value' => 'XL', 'affect_price' => 10.00, 'affect_type' => 0],
            ['name' => 'Finish', 'value' => 'Premium', 'affect_price' => 20, 'affect_type' => 1],
        ]);

        $html = render_product_options_html($options, 200.00);

        // Fixed: $10
        $this->assertStringContainsString(format_price(10.00), $html);
        // Percentage: 20% of $200 = $40
        $this->assertStringContainsString(format_price(40.00), $html);
    }

    public function test_percentage_surcharge_differs_when_base_price_includes_options(): void
    {
        $percentRate = 15;
        $basePrice = 200.00;
        $fixedOption = 30.00;
        $percentageSurcharge = $basePrice * $percentRate / 100; // $30
        $totalWithOptions = $basePrice + $fixedOption + $percentageSurcharge; // $260

        $options = $this->makeMultiOptionData([
            ['name' => 'Engraving', 'value' => 'Yes', 'affect_price' => $fixedOption, 'affect_type' => 0],
            ['name' => 'Premium Wrap', 'value' => 'Gold', 'affect_price' => $percentRate, 'affect_type' => 1],
        ]);

        // Correct: pass product base price
        $htmlCorrect = render_product_options_html($options, $basePrice);
        $this->assertStringContainsString(format_price($percentageSurcharge), $htmlCorrect);

        // Wrong: pass order product price (base + options)
        $wrongSurcharge = $totalWithOptions * $percentRate / 100; // $39
        $htmlWrong = render_product_options_html($options, $totalWithOptions);
        $this->assertStringContainsString(format_price($wrongSurcharge), $htmlWrong);

        // Correct surcharge ($30) != wrong surcharge ($39)
        $this->assertNotEquals($percentageSurcharge, $wrongSurcharge);
    }

    public function test_zero_affect_price_option_shows_no_surcharge(): void
    {
        $options = $this->makeOptionData(0, 0);

        $html = render_product_options_html($options, 100.00);

        $this->assertStringNotContainsString('+ ', $html);
    }

    public function test_returns_empty_when_product_options_disabled(): void
    {
        // Disable product options
        setting()->set(['ecommerce_is_enabled_product_options' => 0])->save();

        $options = $this->makeOptionData(5.00, 0);

        $html = render_product_options_html($options, 100.00);

        $this->assertEmpty($html);

        // Re-enable for other tests
        setting()->set(['ecommerce_is_enabled_product_options' => 1])->save();
    }

    public function test_null_base_price_hides_price_label(): void
    {
        $options = $this->makeOptionData(5.00, 0);

        $html = render_product_options_html($options, null);

        // Should still render option values
        $this->assertStringContainsString('Size XL', $html);
    }
}
