<?php

declare(strict_types=1);

namespace Studio15\FilamentTree\Components;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\QueryBuilder;
use Livewire\Component;
use Livewire\Features\SupportEvents\Event;
use Studio15\FilamentTree\Components\Form\ParentSelect;

/**
 * Header component
 */
final class Header extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    /**
     * @var class-string<TreePage>
     */
    public string $component;

    public function createAction(): CreateAction
    {
        $model = $this->component::getModel();

        $action = CreateAction::make()
            ->after(fn (): Event => $this->dispatch('filament-tree-updated'));

        $this->configureAction($action);

        $action->form([
            ParentSelect::make($model instanceof QueryBuilder ? $model : $model::query()),
            ...$this->component::getCreateForm(),
        ]);

        return $action;
    }

    public function render(): View
    {
        return view('filament-tree::header');
    }

    private function configureAction(CreateAction $action): void
    {
        $model = $this->component::getModel();

        if ($this->component::getModel() instanceof QueryBuilder) {
            $action
                ->model($model->getModel()::class)
                ->mutateFormDataUsing(
                    static fn (array $data): array => [
                        ...$data,
                        ...$model->getModel()->getAttributes(),
                    ],
                )
                ->modelLabel($model->getModel()->getModelLabel())
                ->using(static function (array $data) use ($model): Model {
                    /** @var class-string<Model> $modelClass */
                    $modelClass = $model->getModel()::class;

                    $parent = $modelClass::query()->find($data['parent_id']);
                    unset($data['parent_id']);

                    return $modelClass::create(
                        attributes: $data,
                        parent: $parent,
                    );
                });

            return;
        }

        $action->model($model);
    }
}
