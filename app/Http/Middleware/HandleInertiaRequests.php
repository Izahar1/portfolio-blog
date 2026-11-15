<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'message' => fn (): ?string => $request->session()->get('message'),
            ],
            'site' => fn (): array => Setting::query()->where('group_name', 'site')->get()->mapWithKeys(fn (Setting $setting): array => [$setting->setting_key => $setting->value])->all(),
            'menus' => fn (): array => Menu::query()->with(['items.page', 'items.children.page'])->get()->mapWithKeys(fn (Menu $menu): array => [
                $menu->location => $menu->items->map(fn (MenuItem $item): array => $this->formatMenuItem($item))->all(),
            ])->all(),
        ];
    }

    /** @return array{id: int, label: string, href: ?string, target: string, children: array<int, array<string, mixed>>} */
    private function formatMenuItem(MenuItem $item): array
    {
        return [
            'id' => $item->id,
            'label' => $item->label,
            'href' => $item->link_type === 'page' ? ($item->page ? route('pages.show', $item->page->slug) : null) : $item->url,
            'target' => $item->target,
            'children' => $item->children->map(fn (MenuItem $child): array => [
                'id' => $child->id,
                'label' => $child->label,
                'href' => $child->link_type === 'page' ? ($child->page ? route('pages.show', $child->page->slug) : null) : $child->url,
                'target' => $child->target,
                'children' => [],
            ])->all(),
        ];
    }
}
