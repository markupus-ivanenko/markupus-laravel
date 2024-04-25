<?php

namespace App\Orchid\Screens;

use App\Models\PaymentAddress;
use App\Models\PaymentRate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use App\Models\PaymentValue;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Color;

class PaymentValueScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'paymentValues' => PaymentValue::paginate(20),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Payment Values';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Payment Value')
                ->modal('paymentValueModal')
                ->method('createPaymentValue')
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
            Layout::table('paymentValues', [
                TD::make('', 'Actions')
                    ->render(fn (PaymentValue $paymentValue) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([

                            ModalToggle::make('Edit')
                                ->modal('editPaymentValueModal')
                                ->icon('pencil')
                                ->method('updatePaymentValue')
                                ->asyncParameters([
                                    'paymentValueId' => $paymentValue->id,
                                ]),

                            Button::make('Delete')
                                ->icon('trash')
                                ->method('remove')
                                ->confirm('Are you sure you want to delete this Payment Value?')
                                ->parameters(['id' => $paymentValue->id]),
                        ])),

                TD::make('', 'Payment Address')
                    ->render(fn ($paymentValue) =>
                    Link::make($paymentValue->address->address)
                        ->route('platform.payment-values.details', $paymentValue->id)),
                /*
                TD::make('electricity_day', 'Electricity Day'),
                TD::make('electricity_night', 'Electricity Night'),
                TD::make('gas', 'Gas'),
                TD::make('gas_delivery', 'Gas Delivery'),
                TD::make('water', 'Water'),
                TD::make('heating', 'Heating'),
                */
                TD::make('count_by_rate_no_heating_water_gas_delivery', 'Total Count (without Heating, Water and Gas Delivery)'),
                TD::make('count_by_rate_no_heating', 'Total Count  (without Heating)'),
                TD::make('count_by_rate', 'Total Count'),
                TD::make('', 'Count Date')
                    ->render(
                        function($paymentValue) {
                            return Carbon::parse($paymentValue->count_date)->format('d.m.Y');
                        }
                    ),
                TD::make('created_at', 'Created')
                    ->render(
                        function($paymentValue) {
                            return Carbon::parse($paymentValue->created_at);
                        }
                    ),
                TD::make('updated_at', 'Updated')
                    ->render(
                        function($paymentValue) {
                            return Carbon::parse($paymentValue->updated_at);
                        }
                    ),
            ]),

            Layout::modal('paymentValueModal', Layout::rows([
                Select::make('address')
                    ->fromModel(PaymentAddress::class, 'address'),
                DateTimer::make('paymentValues.count_date')
                    ->title('Date'),
                Input::make('paymentValues.electricity_day')
                    ->title('Electricity Day')
                    ->placeholder('Enter rate for Electricity Day'),
                Input::make('paymentValues.electricity_night')
                    ->title('Electricity Night')
                    ->placeholder('Enter rate for Electricity Night'),
                Input::make('paymentValues.gas')
                    ->title('Gas')
                    ->placeholder('Enter rate for Gas'),
                Input::make('paymentValues.gas_delivery')
                    ->title('Gas Delivery')
                    ->placeholder('Enter rate for Gas Delivery'),
                Input::make('paymentValues.water')
                    ->title('Water')
                    ->placeholder('Enter rate for Water'),
                Input::make('paymentValues.heating')
                    ->title('Heating')
                    ->placeholder('Enter rate for Heating'),
            ])
            )
                ->title('Add Payment Value')
                ->applyButton('Add Payment Value'),

            Layout::modal('editPaymentValueModal', Layout::rows([
                Select::make('paymentValue.address_id')
                    ->options(
                        PaymentAddress::all()->pluck('address', 'id')->toArray()
                    ),
                DateTimer::make('paymentValue.count_date')
                    ->title('Date'),
                Input::make('paymentValue.electricity_day')
                    ->title('Electricity Day')
                    ->placeholder('Enter rate for Electricity Day'),
                Input::make('paymentValue.electricity_night')
                    ->title('Electricity Night')
                    ->placeholder('Enter rate for Electricity Night'),
                Input::make('paymentValue.gas')
                    ->title('Gas')
                    ->placeholder('Enter rate for Gas'),
                Input::make('paymentValue.gas_delivery')
                    ->title('Gas Delivery')
                    ->placeholder('Enter rate for Gas Delivery'),
                Input::make('paymentValue.water')
                    ->title('Water')
                    ->placeholder('Enter rate for Water'),
                Input::make('paymentValue.heating')
                    ->title('Heating')
                    ->placeholder('Enter rate for Heating'),
            ])
            )
                ->title('Edit Payment Value')
                ->applyButton('Edit Payment Value')
                ->async('asyncGetPayment'),
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function createPaymentValue(Request $request)
    {
        $payment_rate = PaymentRate::findOrFail($request->input('address'));

        // Validate form data, save task to database, etc.

        $electricity_day =  is_numeric($request->input('paymentValues.electricity_day')) ? $request->input('paymentValues.electricity_day') : 0;
        $electricity_night=  is_numeric($request->input('paymentValues.electricity_night')) ? $request->input('paymentValues.electricity_night') : 0;
        $gas =  is_numeric($request->input('paymentValues.gas')) ? $request->input('paymentValues.gas') : 0;

        $actual_electricity_day = 0;
        $actual_electricity_night = 0;
        $actual_gas = 0;

        $lastRecord = PaymentValue::latest()->first();

        if ($lastRecord) {

            $last_electricity_day =  is_numeric($lastRecord->electricity_day) ? $lastRecord->electricity_day : 0;
            $last_electricity_night =  is_numeric($lastRecord->electricity_night) ? $lastRecord->electricity_night : 0;
            $last_gas =  is_numeric($lastRecord->gas) ? $lastRecord->gas : 0;

            $actual_electricity_day = $electricity_day - $last_electricity_day;
            $actual_electricity_night = $electricity_night - $last_electricity_night;
            $actual_gas = $gas - $last_gas;
        }

        $payment_val = new PaymentValue();
        $payment_val->address_id = $request->input('address');
        $payment_val->electricity_day = $request->input('paymentValues.electricity_day');
        $payment_val->electricity_night = $request->input('paymentValues.electricity_night');
        $payment_val->gas = $request->input('paymentValues.gas');
        $payment_val->gas_delivery = $payment_rate->gas_delivery;
        $payment_val->water = $payment_rate->water;
        $payment_val->heating = $request->input('paymentValues.heating');
        $payment_val->count_date = $request->input('paymentValues.count_date');
//        $payment_val->count_by_rate_no_heating = $count_by_rate_no_heating;
//        $payment_val->count_by_rate = $count_by_rate;
        $payment_val->user_id = Auth::id();

        //Counts Actual Data
        $payment_val->actual_electricity_day = $actual_electricity_day;
        $payment_val->actual_electricity_night = $actual_electricity_night;
        $payment_val->actual_gas = $actual_gas;

        //Counts Actual Sum
        $count_electricity_day = ($payment_rate->electricity_day) ? $payment_rate->electricity_day * $actual_electricity_day : 0;
        $count_electricity_night = ($payment_rate->electricity_night) ? $payment_rate->electricity_night * $actual_electricity_night : 0;
        $count_gas = ($payment_rate->gas) ? $payment_rate->gas * $actual_gas : 0;

        $payment_val->actual_sum_electricity_day = $count_electricity_day;
        $payment_val->actual_electricity_night = $count_electricity_night;
        $payment_val->actual_sum_gas = $count_gas;

        $payment_val->count_by_rate_no_heating_water_gas_delivery = $count_electricity_day + $count_electricity_night + $count_gas;

        $payment_val->count_by_rate_no_heating = $count_electricity_day + $count_electricity_night + $count_gas + $payment_rate->gas_delivery + $payment_rate->water;
        $payment_val->count_by_rate = $count_electricity_day + $count_electricity_night + $count_gas + $payment_rate->gas_delivery + $payment_rate->water + $request->input('paymentValues.heating');

        $payment_val->save();
    }

    public function updatePaymentValue(Request $request)
    {
        $paymentValue = PaymentValue::findOrFail($request->input('paymentValueId'))
            ->update($request->paymentValue);
        Toast::info('Data has been updated');
    }

    public function asyncGetPayment(Request $request)
    {

        return [
            'paymentValue' => PaymentValue::find($request->input('paymentValueId')),
//            'paymentValue' => '1111',
        ];
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Request $request)
    {
        $paymentValue = PaymentValue::find($request->input('id'));
        if ($paymentValue) {
            $paymentValue->delete();

            Alert::info('You have successfully deleted Payment Values.');
        }

        return redirect()->route('platform.payment-values');
    }
}
