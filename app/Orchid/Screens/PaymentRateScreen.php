<?php

namespace App\Orchid\Screens;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Screen;
use App\Models\PaymentRate;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use App\Models\PaymentAddress;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Color;

class PaymentRateScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'paymentRates' => PaymentRate::paginate(20),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Payment Rate';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Payment Rate')
                ->modal('paymentRateModal')
                ->method('createPaymentRate')
                ->icon('plus'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('paymentRates', [
                TD::make('', 'Actions')
                    ->render(
                        function($paymentRate) {
                            return
                                Group::make([
                                    ModalToggle::make('Edit')
                                        ->modal('editPaymentRateModal')
                                        ->icon('pencil')
                                        ->method('updatePaymentRate')
                                        ->asyncParameters([
                                            'paymentRateId' => $paymentRate->id,
                                        ])
                                        ->class('mb-3 btn btn-primary'),
                                    Button::make('Remove')
                                        ->icon('trash')
                                        ->method('remove')
                                        ->confirm('Are you sure you want to delete this Payment Rate?')
                                        ->parameters(['id' => $paymentRate->id])
                                        ->type(Color::DANGER),
                                ]);
                        }

                    ),
                TD::make('', 'Payment Address')
                    ->render(
                        function($paymentRate) {
                            return ModalToggle::make($paymentRate->address->address)
                                ->modal('editPaymentRateModal')
                                ->icon('pencil')
                                ->method('updatePaymentRate')
                                ->asyncParameters([
                                    'paymentRateId' => $paymentRate->id,
                                ]);
                        }
                    ),
                TD::make('electricity_day', 'Electricity Day'),
                TD::make('electricity_night', 'Electricity Night'),
                TD::make('gas', 'Gas'),
                TD::make('gas_delivery', 'Gas Delivery'),
                TD::make('water', 'Water'),
                TD::make('heating', 'Heating'),
                TD::make('created_at', 'Created At')
                    ->render(
                        function($paymentRate) {
                            return Carbon::parse($paymentRate->created_at);
                        }
                    ),
                TD::make('updated_at', 'Updated At')
                    ->render(
                        function($paymentRate) {
                            return Carbon::parse($paymentRate->updated_at);
                        }
                    ),
            ]),

            Layout::modal('paymentRateModal', Layout::rows([
                Select::make('address')
                    ->fromModel(PaymentAddress::class, 'address'),
                DateTimer::make('paymentRates.rate_date')
                    ->title('Date'),
                Input::make('paymentRates.electricity_day')
                    ->title('Electricity Day')
                    ->placeholder('Enter rate for Electricity Day'),
                Input::make('paymentRates.electricity_night')
                    ->title('Electricity Night')
                    ->placeholder('Enter rate for Electricity Night'),
                Input::make('paymentRates.gas')
                    ->title('Gas')
                    ->placeholder('Enter rate for Gas'),
                Input::make('paymentRates.gas_delivery')
                    ->title('Gas Delivery')
                    ->placeholder('Enter rate for Gas Delivery'),
                Input::make('paymentRates.water')
                    ->title('Water')
                    ->placeholder('Enter rate for Water'),
                Input::make('paymentRates.heating')
                    ->title('Heating')
                    ->placeholder('Enter rate for Heating'),
            ])
            )
                ->title('Add Payment Address')
                ->applyButton('Add Payment Address'),

            Layout::modal('editPaymentRateModal', Layout::rows([
                Select::make('paymentRate.address_id')
                    ->options(
                        PaymentAddress::all()->pluck('address', 'id')->toArray()
                    ),
                DateTimer::make('paymentRate.rate_date')
                    ->title('Date'),
                Input::make('paymentRate.electricity_day')
                    ->title('Electricity Day')
                    ->placeholder('Enter rate for Electricity Day'),
                Input::make('paymentRate.electricity_night')
                    ->title('Electricity Night')
                    ->placeholder('Enter rate for Electricity Night'),
                Input::make('paymentRate.gas')
                    ->title('Gas')
                    ->placeholder('Enter rate for Gas'),
                Input::make('paymentRate.gas_delivery')
                    ->title('Gas Delivery')
                    ->placeholder('Enter rate for Gas Delivery'),
                Input::make('paymentRate.water')
                    ->title('Water')
                    ->placeholder('Enter rate for Water'),
                Input::make('paymentRate.heating')
                    ->title('Heating')
                    ->placeholder('Enter rate for Heating'),
            ])
            )
                ->title('Edit Payment Value')
                ->applyButton('Edit Payment Value')
                ->async('asyncGetPaymentRate'),

        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function createPaymentRate(Request $request)
    {
        // Validate form data, save task to database, etc.

        $paymentRate = new PaymentRate();
        $paymentRate->address_id = $request->input('address');
        $paymentRate->electricity_day = $request->input('paymentRates.electricity_day');
        $paymentRate->electricity_night = $request->input('paymentRates.electricity_night');
        $paymentRate->gas = $request->input('paymentRates.gas');
        $paymentRate->gas_delivery = $request->input('paymentRates.gas_delivery');
        $paymentRate->water = $request->input('paymentRates.water');
        $paymentRate->heating = $request->input('paymentRates.heating');
        $paymentRate->rate_date = $request->input('paymentRates.rate_date');
        $paymentRate->user_id = Auth::id();
        $paymentRate->save();
    }

    public function updatePaymentRate(Request $request)
    {
        $paymentRate= PaymentRate::findOrFail($request->input('paymentRateId'))
            ->update($request->paymentRate);
        Toast::info('Data has been updated');
    }

    public function asyncGetPaymentRate(Request $request) {

        return [
            'paymentRate' => PaymentRate::find($request->input('paymentRateId')),
        ];
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Request $request)
    {
        $paymentRate = PaymentRate::find($request->input('id'));
        if ($paymentRate) {
            $paymentRate->delete();

            Alert::info('You have successfully deleted Payment Rate.');
        }

        return redirect()->route('platform.payment-rate');
    }
}
