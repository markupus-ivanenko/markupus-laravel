<?php

namespace App\Orchid\Screens;

use App\Models\Country;
use App\Models\PaymentAddress;
use App\Models\Region;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Color;

class PaymentAddressScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'paymentAddresses' => PaymentAddress::paginate(20),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Payment Addresses';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Payment Address')
                ->modal('paymentAddressModal')
                ->method('createPaymentAddress')
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
            Layout::table('paymentAddresses', [
                TD::make('', 'Actions')
                    ->render(
                        function($paymentAddress) {
                            return
                                Group::make([
                                    ModalToggle::make('Edit')
                                        ->modal('editPaymentAddressModal')
                                        ->icon('pencil')
                                        ->method('updatePaymentAddress')
                                        ->asyncParameters([
                                            'paymentAddressId' => $paymentAddress->id,
                                        ])
                                        ->class('mb-3 btn btn-primary'),
                                    Button::make('Remove')
                                        ->icon('trash')
                                        ->method('remove')
                                        ->confirm('Are you sure you want to delete this Payment Address?')
                                        ->parameters(['id' => $paymentAddress->id])
                                        ->type(Color::DANGER),
                                ]);
                        }

                    ),
                TD::make('', 'Payment Address')
                    ->width('50%')
                    ->render(
                        function($paymentAddress) {
                            return ModalToggle::make($paymentAddress->address)
                                ->modal('editPaymentAddressModal')
                                ->icon('pencil')
                                ->method('updatePaymentAddress')
                                ->asyncParameters([
                                    'paymentAddressId' => $paymentAddress->id,
                                ]);
                        }
                    ),
                TD::make('', 'Region')
                    ->render(
                        function($paymentAddress) {
                            $country = $paymentAddress->region->country->country;
                            return $country . ', ' . $paymentAddress->region->region;
                        }
                    ),
                TD::make('created_at', 'Created At')
                    ->render(
                        function($paymentAddress) {
                            return Carbon::parse($paymentAddress->created_at);
                        }
                    ),
                TD::make('updated_at', 'Updated At')
                    ->render(
                        function($paymentAddress) {
                            return Carbon::parse($paymentAddress->updated_at);
                        }
                    ),
            ]),

            Layout::modal('paymentAddressModal', Layout::rows([
                Input::make('paymentAddresses.address')
                    ->title('Payment Address')
                    ->placeholder('Enter payment address'),
                Select::make('regions')
                    ->fromModel(Region::class, 'region'),
            ])
            )
                ->title('Add Payment Address')
                ->applyButton('Add Payment Address'),

            Layout::modal('editPaymentAddressModal', Layout::rows([
                Input::make('paymentAddress.address')
                    ->title('Payment Address')
                    ->placeholder('Enter payment address'),
                Select::make('paymentAddress.region_id')
                    ->options(
                        Region::all()->pluck('region', 'id')->toArray()
                    ),
//                    ->fromModel(Region::class, 'region'),
            ])
            )
                ->title('Edit Payment Address')
                ->applyButton('Edit Payment Address')
                ->async('asyncGetPaymentAddress'),

            Layout::modal('editRegionModal', Layout::rows([
                Input::make('region.region')
                    ->title('Region')
                    ->placeholder('Enter region'),
                Select::make('region.country_id')
                    ->options(
                        Country::all()->pluck('country', 'id')->toArray()
                    ),
//                    ->fromModel(Region::class, 'region'),
            ])
            )
                ->title('Edit Region')
                ->applyButton('Edit Region')
                ->async('asyncGetRegions'),
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function createPaymentAddress(Request $request)
    {
        // Validate form data, save task to database, etc.
        $request->validate([
            'paymentAddresses.address' => 'required|max:255',
        ]);

        $region = new PaymentAddress();
        $region->address = $request->input('paymentAddresses.address');
        $region->region_id = $request->input('regions');
        $region->user_id = Auth::id();
        $region->save();
    }

    public function updatePaymentAddress(Request $request)
    {
        $paymentAddress = PaymentAddress::findOrFail($request->input('paymentAddressId'))
            ->update($request->paymentAddress);
        Toast::info('Data has been updated');
    }

    public function asyncGetPaymentAddress(Request $request) {

        return [
            'paymentAddress' => PaymentAddress::find($request->input('paymentAddressId')),
        ];
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Request $request)
    {
        $paymentAddress = PaymentAddress::find($request->input('id'));
        if ($paymentAddress) {
            $paymentAddress->delete();

            Alert::info('You have successfully deleted Payment Address.');
        }

        return redirect()->route('platform.payment-address');
    }
}
