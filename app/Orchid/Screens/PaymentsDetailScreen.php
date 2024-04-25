<?php

namespace App\Orchid\Screens;

use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use App\Models\PaymentValue;
use Orchid\Screen\Sight;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;

class PaymentsDetailScreen extends Screen
{

    /**
     * @var PaymentValue
     */
    public $PaymentValue;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $id = $request->route('id');
        return [
            'paymen_detail' => PaymentValue::findOrFail($id),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'PaymentsDetailScreen';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::legend('paymen_detail', [
                Sight::make('id'),
                Sight::make('electricity_day'),
                Sight::make('actual_electricity_day'),

                Sight::make('electricity_day'),
                Sight::make('actual_electricity_day'),

                Sight::make('actual_sum_electricity_day'),
                Sight::make('electricity_night'),

                Sight::make('actual_electricity_night'),
                Sight::make('actual_sum_electricity_night'),

                Sight::make('gas'),
                Sight::make('actual_gas'),

                Sight::make('actual_sum_gas'),
                Sight::make('gas_delivery'),

                Sight::make('water'),
                Sight::make('actual_water'),

                Sight::make('actual_sum_water'),
                Sight::make('heating'),

                Sight::make('count_by_rate_no_heating_water_gas_delivery'),
                Sight::make('count_by_rate_no_heating'),

                Sight::make('count_by_rate'),
                Sight::make('count_date'),

                Sight::make('created_at', 'Created'),
                Sight::make('updated_at', 'Updated'),
                Sight::make('Simple Text')->render(fn () => 'This is a wider card with supporting text below as a natural lead-in to additional content.'),
                Sight::make('Action')->render(fn () => Button::make('Show toast')
                    ->type(Color::BASIC)
                    ->method('showToast')),
            ])->title('User'),
        ];
    }
}
