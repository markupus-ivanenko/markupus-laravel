<?php

namespace App\Orchid\Screens;

use App\Models\Country;
use App\Models\Region;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

class RegionScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'regions' => Region::paginate(20),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Regions';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Region')
                ->modal('regionModal')
                ->method('createRegion')
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
            Layout::table('regions', [
                TD::make('', 'Actions')
                    ->render(
                        function($region) {
                            return
                                Group::make([
                                    ModalToggle::make('Edit')
                                        ->modal('editRegionModal')
                                        ->icon('pencil')
                                        ->method('updateRegion')
                                        ->asyncParameters([
                                            'regionId' => $region->id,
                                        ])
                                        ->class('mb-3 btn btn-primary'),
                                    Button::make('Remove')
                                        ->icon('trash')
                                        ->method('remove')
                                        ->confirm('Are you sure you want to delete this Region?')
                                        ->parameters(['id' => $region->id])
                                        ->type(Color::DANGER),
                                ]);
                        }

                    ),
                TD::make('', 'Region')
                    ->width('50%')
                    ->render(
                        function($region) {
                            return ModalToggle::make($region->region)
                                ->modal('editRegionModal')
                                ->icon('pencil')
                                ->method('updateRegion')
                                ->asyncParameters([
                                    'regionId' => $region->id,
                                ]);
                        }
                    ),
                TD::make('', 'Country')
                   ->render(
                       function($region) {
                           return $region->country->country;
                       }
                   ),
                TD::make('created_at', 'Created At')
                    ->render(
                        function($region) {
                            return Carbon::parse($region->created_at);
                        }
                    ),
                TD::make('updated_at', 'Updated At')
                    ->render(
                        function($region) {
                            return Carbon::parse($region->updated_at);
                        }
                    ),
            ]),

            Layout::modal('regionModal', Layout::rows([
                Input::make('regions.region')
                    ->title('Region')
                    ->placeholder('Enter region'),
                Select::make('countries')
                    ->fromModel(Country::class, 'country'),
                ])
            )
                ->title('Add Region')
                ->applyButton('Add Region'),

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
    public function createRegion(Request $request)
    {
        // Validate form data, save task to database, etc.
        $request->validate([
            'regions.region' => 'required|max:255',
        ]);

        $region = new Region();
        $region->region = $request->input('regions.region');
        $region->country_id = $request->input('countries');
        $region->save();
    }

    public function updateRegion(Request $request)
    {
        $region = Region::findOrFail($request->input('regionId'))
            ->update($request->region);
        Toast::info('Data has been updated');
    }

    public function asyncGetRegions(Request $request) {
        return [
            'region' => Region::find($request->input('regionId')),
        ];
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Request $request)
    {
        $region = Region::find($request->input('id'));
        if ($region) {
            $region->delete();

            Alert::info('You have successfully deleted Region.');
        }

        return redirect()->route('platform.regions');
    }
}
