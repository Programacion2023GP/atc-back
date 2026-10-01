<?php

namespace Tests\Unit;

use App\Http\Controllers\GomezApp\InternalRequestController;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class InternalRequestRulesTest extends TestCase
{
    private function invoke(string $method, ...$arguments)
    {
        $reflection = new ReflectionMethod(InternalRequestController::class, $method);
        $reflection->setAccessible(true);
        return $reflection->invoke(new InternalRequestController(), ...$arguments);
    }

    public function test_folio_uses_five_digit_sequence(): void
    {
        $this->assertSame('OM-2026-00001', $this->invoke('formatFolio', 'OM', 2026, 1));
        $this->assertSame('INT-2026-12000', $this->invoke('formatFolio', 'INT', 2026, 12000));
    }

    public function test_business_day_due_date_skips_weekend(): void
    {
        $friday = Carbon::parse('2026-10-02 09:00:00');
        $this->assertSame('2026-10-05', $this->invoke('addBusinessDays', $friday, 1)->toDateString());
        $this->assertSame('2026-10-07', $this->invoke('addBusinessDays', $friday, 3)->toDateString());
    }

    public function test_editor_document_accepts_only_supported_nodes_and_marks(): void
    {
        $valid = [
            'type' => 'doc',
            'content' => [[
                'type' => 'paragraph',
                'attrs' => ['textAlign' => 'center', 'indent' => 1],
                'content' => [['type' => 'text', 'text' => 'Oficio', 'marks' => [['type' => 'bold']]]],
            ]],
        ];
        $image = ['type' => 'doc', 'content' => [['type' => 'image', 'attrs' => ['src' => 'data:image/png;base64,x']]]];
        $color = [
            'type' => 'doc',
            'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'No', 'marks' => [['type' => 'textStyle']]]]]],
        ];
        $this->assertTrue($this->invoke('validNode', $valid));
        $this->assertFalse($this->invoke('validNode', $image));
        $this->assertFalse($this->invoke('validNode', $color));
        $this->assertSame('Oficio', $this->invoke('bodyText', $valid));
    }
}
