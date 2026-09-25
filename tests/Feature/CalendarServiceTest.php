<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\Weekend;
use App\Services\Hr\CalendarService;
use Database\Seeders\HolidaySeeder;
use Database\Seeders\WeekendSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CalendarServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CalendarService $calendarService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            WeekendSeeder::class,
            HolidaySeeder::class,
        ]);

        $this->calendarService = app(CalendarService::class);
    }

    public function test_is_weekend_detects_friday_as_weekend(): void
    {
        // 2026-09-25 is a Friday
        $this->assertTrue($this->calendarService->isWeekend('2026-09-25'));

        // 2026-09-26 is a Saturday
        $this->assertFalse($this->calendarService->isWeekend('2026-09-26'));

        // 2026-09-27 is a Sunday
        $this->assertFalse($this->calendarService->isWeekend('2026-09-27'));

        // 2026-09-28 is a Monday
        $this->assertFalse($this->calendarService->isWeekend('2026-09-28'));
    }

    public function test_is_weekend_updates_when_weekend_config_changes(): void
    {
        // Change Saturday to also be a weekend
        Weekend::where('day_of_week', 6)->update(['is_weekend' => true]);

        // 2026-09-26 is a Saturday
        $this->assertTrue($this->calendarService->isWeekend('2026-09-26'));
    }

    public function test_is_holiday_detects_configured_holidays(): void
    {
        $year = (int) date('Y');

        // Feb 21 is International Mother Language Day
        $this->assertTrue($this->calendarService->isHoliday("{$year}-02-21"));

        // Mar 26 is Independence Day
        $this->assertTrue($this->calendarService->isHoliday("{$year}-03-26"));

        // A regular non-holiday date
        $this->assertFalse($this->calendarService->isHoliday("{$year}-02-22"));
    }

    public function test_is_holiday_cache_invalidates_on_model_save(): void
    {
        $this->assertFalse($this->calendarService->isHoliday('2026-11-11'));

        Holiday::create([
            'title' => 'Special Team Break',
            'from_date' => '2026-11-11',
            'to_date' => '2026-11-11',
            'type' => 'company',
            'status' => 'active',
        ]);

        $this->assertTrue($this->calendarService->isHoliday('2026-11-11'));
    }

    public function test_working_days_between_excludes_weekends_and_holidays(): void
    {
        // Configure Saturday & Sunday as weekends for this test
        Weekend::whereIn('day_of_week', [0, 6])->update(['is_weekend' => true]);
        Weekend::where('day_of_week', 5)->update(['is_weekend' => false]);
        $this->calendarService->clearCache();

        // 2026-10-05 (Monday) to 2026-10-09 (Friday) is 5 weekdays
        $days = $this->calendarService->workingDaysBetween('2026-10-05', '2026-10-09');
        $this->assertEquals(5, $days);

        // Add a holiday on Wednesday (2026-10-07)
        Holiday::create([
            'title' => 'Test Day Off',
            'from_date' => '2026-10-07',
            'to_date' => '2026-10-07',
            'type' => 'company',
            'status' => 'active',
        ]);

        $daysWithHoliday = $this->calendarService->workingDaysBetween('2026-10-05', '2026-10-09');
        $this->assertEquals(4, $daysWithHoliday);
    }
}
