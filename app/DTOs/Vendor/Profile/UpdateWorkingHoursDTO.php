<?php

namespace App\DTOs\Vendor\Profile;

use Illuminate\Http\Request;

readonly class UpdateWorkingHoursDTO
{
    /**
     * @param  array<int, array{day_of_week: int, open_time: string, close_time: string}>  $workingHours
     */
    public function __construct(
        public array $workingHours,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            workingHours: $data['working_hours'],
        );
    }
}
