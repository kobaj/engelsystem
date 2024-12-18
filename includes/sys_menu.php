<?php

use Engelsystem\Models\Question;
use Engelsystem\UserHintsRenderer;

/**
 * Render the user hints
 *
 * @return string
 */
function header_render_hints()
{
    $user = auth()->user();

    if ($user) {
        $hints_renderer = new UserHintsRenderer();

        $hints_renderer->addHint(user_angeltypes_unconfirmed_hint());
        $hints_renderer->addHint(render_user_departure_date_hint());
        $hints_renderer->addHint(user_driver_license_required_hint());
        $hints_renderer->addHint(user_ifsg_certificate_required_hint());

        // Important hints:
        $hints_renderer->addHint(render_user_freeloader_hint(), true);
        $hints_renderer->addHint(render_user_arrived_hint(true), true);
        $hints_renderer->addHint(render_user_pronoun_hint(), true);
        $hints_renderer->addHint(render_user_firstname_hint(), true);
        $hints_renderer->addHint(render_user_lastname_hint(), true);
        $hints_renderer->addHint(render_user_goodie_hint(), true);
        $hints_renderer->addHint(render_user_dect_hint(), true);
        $hints_renderer->addHint(render_user_mobile_hint(), true);

        return $hints_renderer->render();
    }

    return '';
}

function make_navigation(): array
{
    $pages = [
        // path          => name,
        // path          => [name, permission],
        'news'           => 'news.title',
        'user_shifts'    => 'general.shifts',
        'locations'      => ['location.locations', 'locations.view'],
    ];

    $menu = make_navigation_group($pages);

    foreach (config('header_items', []) as $title => $options) {
        $menu[$title] = $options;
    }

    $gofurs_pages = [
        // gofurs
        'admin_arrive'       => [admin_arrive_title(), 'users.arrive.list'],
        'admin_active'       => ['Active angels', 'admin_user'],
        'users'              => ['All Angels', 'admin_user'],
        'admin_free'         => ['Free angels', 'admin_user'],
        'angeltypes'         => ['angeltypes.angeltypes', 'admin_angel_types'],
    ];

    $shift_pages = [
        // shifts
        'admin_shifts'       => ['Create shifts', 'admin_shifts'],
        'admin/shifttypes'   => ['shifttype.shifttypes', 'shifttypes.edit'],
        'admin/schedule'     => ['schedule.import', 'schedule.import'],
    ];

    $admin_pages = [
        // Other admin stuff
        'admin_groups'       => ['Group rights', 'admin_groups'],
        'admin/logs'         => ['log.log', 'admin_log'],
        'admin/config'       => ['config.config', 'config.edit'],
    ];

    if (config('autoarrive')) {
        unset($gofurs_pages['admin_arrive']);
    }

    $gofurs_menu = make_navigation_group($gofurs_pages);
    $shift_menu = make_navigation_group($shift_pages);
    $admin_menu = make_navigation_group($admin_pages);

    $menu['Gofurs'] = [$gofurs_menu, $gofurs_menu ? null : 'hide', true];
    $menu['Shift'] = [$shift_menu, $shift_menu ? null : 'hide', true];
    $menu['Admin'] = [$admin_menu, $admin_menu ? null : 'hide', true];

    return $menu;
}

function make_navigation_group($pages)
{
    $menu = [];
    foreach ($pages as $menu_page => $options) {
        $options = (array) $options;
        if (!auth()->can($options[1] ?? $menu_page)) {
            continue;
        }

        $menu[$options[0]] = [
            url(str_replace('_', '-', $menu_page)),
            $options[1] ?? $menu_page,
        ];
    }

    return $menu;
}


/**
 * Renders language selection.
 *
 * @return array
 */
function make_language_select()
{
    $request = app('request');
    $activeLocale = session()->get('locale');

    $items = [];
    foreach (config('locales') as $locale => $name) {
        $url = url($request->getPathInfo(), [...$request->getQueryParams(), 'set-locale' => $locale]);

        $items[] = toolbar_dropdown_item(
            htmlspecialchars($url),
            $name,
            $locale == $activeLocale
        );
    }
    return $items;
}

/**
 * Renders a hint for new questions to answer.
 *
 * @return string|null
 */
function admin_new_questions()
{
    $currentPage = request()->query->get('p') ?: str_replace('-', '_', request()->path());
    if (!auth()->can('question.edit') || $currentPage == 'admin/questions') {
        return null;
    }

    $unanswered_questions = Question::unanswered()->count();
    if (!$unanswered_questions) {
        return null;
    }

    return '<a href="' . url('/admin/questions') . '">'
        . __('There are unanswered questions!')
        . '</a>';
}
