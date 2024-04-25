<?php

namespace App\Orchid\Screens;

use App\Models\PaymentAddress;
use App\Models\Region;
use Carbon\Carbon;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Input;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

use App\Models\Country;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Color;

class CountryScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'countries' => Country::paginate(20),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Countries';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Country')
                ->modal('countryModal')
                ->method('createCountry')
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

            Layout::table('countries', [
                TD::make('', 'Actions')
                    ->render(
                        function($country) {
                            return
                                Group::make([
                                    ModalToggle::make('Edit')
                                        ->modal('editCountryModal')
                                        ->icon('pencil')
                                        ->method('updateCountry')
                                        ->asyncParameters([
                                            'countryId' => $country->id,
                                        ])
                                        ->class('mb-3 btn btn-primary'),
                                    Button::make('Remove')
                                        ->icon('trash')
                                        ->method('remove')
                                        ->confirm('Are you sure you want to delete this Country?')
                                        ->parameters(['id' => $country->id])
                                        ->type(Color::DANGER),
                                ]);
                        }

                    ),
                TD::make('', 'Country')->width('50%')
                    ->render(
                        function($country) {
                            return ModalToggle::make($country->country)
                                ->modal('editCountryModal')
                                ->icon('pencil')
                                ->method('updateCountry')
                                ->asyncParameters([
                                    'countryId' => $country->id,
                                ]);
                        }
                    ),
                TD::make('created_at', 'Created At')
                    ->render(
                        function($country) {
                            return Carbon::parse($country->created_at);
                        }
                    ),
                TD::make('updated_at', 'Updated At')
                    ->render(
                        function($country) {
                            return Carbon::parse($country->updated_at);
                        }
                    ),
            ]),

            Layout::modal('countryModal', Layout::rows([
                Input::make('countries.country')
                    ->title('Country')
                    ->placeholder('Enter country'),
                ]))
                ->title('Add Country')
                ->applyButton('Add Country'),

            Layout::modal('editCountryModal', Layout::rows([
                Input::make('country.country')
                    ->title('Country')
                    ->placeholder('Enter country'),
            ]))
                ->title('Update Country')
                ->applyButton('Update Country')
                ->async('asyncGetCountry'),

        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function createCountry(Request $request)
    {
        // Validate form data, save task to database, etc.
        $request->validate([
            'countries.country' => 'required|max:255',
        ]);

        $country = new Country();
        $country->country = $request->input('countries.country');
        $country->save();
    }

    public function updateCountry(Request $request)
    {
        $region = Country::findOrFail($request->input('countryId'))
            ->update($request->country);
        Toast::info('Data has been updated');
    }

    public function asyncGetCountry(Request $request) {
        return [
            'country' => Country::find($request->input('countryId')),
        ];
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Request $request)
    {
        $country = Country::find($request->input('id'));
        if ($country) {
            $country->delete();

            Alert::info('You have successfully deleted Country.');
        }

        return redirect()->route('platform.countries');
    }
}
