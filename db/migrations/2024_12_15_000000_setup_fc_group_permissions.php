<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

// The following groups currently exist
//
// Angel
// API
// Bureaucrat
// Developer
// Goodie Manager
// Guest
// Shift Coordinator
// Voucher Angel
// Welcome Angel

class SetupFcGroupPermissions extends Migration
{
    protected Connection $db;
    protected int $goodieManager = 50;

    public function __construct(SchemaBuilder $schema)
    {
        parent::__construct($schema);
        $this->db = $this->schema->getConnection();
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        // My philosophy is that a privilege should only belong to a single group.
        // And that a user should be fully capable of joining multiple groups.
        //
        // The only exception is for Guest (not logged in user) which is mutually exclusive with Gofur.
        // And Developer, which if we don't give it admin_users permission we can lock ourselves out.

        $this->upAngelGroup();
        $this->upShiftCoordinator();
        $this->upBureaucrat();
        $this->upGoodieManager();
        $this->upVoucherAngel();
        $this->upWelcomeAngel();
        $this->upGuest();
        $this->upDeveloper();

        // The following privilege are not set anywhere
        // faq.edit, faq.view, news_comments, question.add, question.edit,
        // user.goodie.edit, user_meetings, user_messages, voucher.edit
    }

    private function upAngelGroup(): void
    {
        $group = 'Gofur';
        $this->renameGroup('Angel', $group);

        $this->removeGroupPrivilege($group, 'faq.view');
        $this->removeGroupPrivilege($group, 'news_comments');
        $this->removeGroupPrivilege($group, 'question.add');
        $this->removeGroupPrivilege($group, 'user_meetings');
        $this->removeGroupPrivilege($group, 'user_messages');

        $this->addGroupPrivilege($group, 'admin_user_worklog');

        // The final set of privileges should be
        // admin_user_worklog, angeltypes, atom, ical, locations.view, logout, news,
        // shifts_json_export, user_angeltypes, user_myshifts, user_settings, user_shifts
    }

    private function upShiftCoordinator(): void
    {
        $group = 'Shift Coordinator';
        $this->removeGroupPrivilege($group, 'admin_log');
        $this->removeGroupPrivilege($group, 'admin_news');
        $this->removeGroupPrivilege($group, 'admin_user');
        $this->removeGroupPrivilege($group, 'admin_user_angeltypes');
        $this->removeGroupPrivilege($group, 'admin_user_worklog');
        $this->removeGroupPrivilege($group, 'faq.edit');
        $this->removeGroupPrivilege($group, 'question.edit');
        $this->removeGroupPrivilege($group, 'register');
        $this->removeGroupPrivilege($group, 'user.drive.edit');
        $this->removeGroupPrivilege($group, 'user.goodie.edit');
        $this->removeGroupPrivilege($group, 'user.ifsg.edit');
        $this->removeGroupPrivilege($group, 'voucher.edit');

        $this->addGroupPrivilege($group, 'shifttypes.edit');

        // The final set of privileges should be
        // admin_active, admin_arrive, admin_free, admin_shifts, shifttypes.edit,
        // shifttypes.view, user.info.show, user_shifts_admin, users.arrive.list
    }

    private function upBureaucrat(): void
    {
        $group = 'Staff';
        $this->renameGroup('Bureaucrat', $group);

        $this->removeGroupPrivilege($group, 'api');
        $this->removeGroupPrivilege($group, 'shifttypes.edit');

        $this->addGroupPrivilege($group, 'admin_news');
        $this->addGroupPrivilege($group, 'admin_user');
        $this->addGroupPrivilege($group, 'admin_user_angeltypes');

        // The final set of privileges should be
        // admin_angel_types, admin_log, admin_news, admin_user, admin_user_angeltypes,
        // locations.edit, logs.all, news.highlight, schedule.import, user.fa.edit,
        // user.info.edit, user.nick.edit
    }

    private function upGoodieManager(): void
    {
        $group = 'unused_1';
        $this->renameGroup('Goodie Manager', $group);

        $this->removeGroupPrivilege($group, 'admin_active');
        $this->removeGroupPrivilege($group, 'admin_arrive');
        $this->removeGroupPrivilege($group, 'angeltype.goodie.list');
        $this->removeGroupPrivilege($group, 'user.goodie.edit');
        $this->removeGroupPrivilege($group, 'users.arrive.list');

        // The final set of privileges should be empty
    }

    private function upVoucherAngel(): void
    {
        $group = 'unused_2';
        $this->renameGroup('Voucher Angel', $group);

        $this->removeGroupPrivilege($group, 'users.arrive.list');
        $this->removeGroupPrivilege($group, 'voucher.edit');

        // The final set of privileges should be empty
    }

    private function upWelcomeAngel(): void
    {
        $group = 'unused_3';
        $this->renameGroup('Welcome Angel', $group);

        $this->removeGroupPrivilege($group, 'admin_arrive');
        $this->removeGroupPrivilege($group, 'users.arrive.list');

        // The final set of privileges should be empty
    }

    private function upGuest(): void
    {
        $group = 'Guest';

        $this->removeGroupPrivilege($group, 'faq.view');

        $this->addGroupPrivilege($group, 'news');

        // The final set of privileges should be
        // login, news, register, start
    }

    private function upDeveloper(): void
    {
        $group = 'Developer';

        $this->addGroupPrivilege($group, 'admin_user');

        // The final set of privileges should be
        // admin_groups, admin_user, config.edit
    }

    private function renameGroup($groupNameOld, $groupNameNew): void
    {
        FcMigrationUtils::renameGroup($this->db, $groupNameOld, $groupNameNew);
    }

    private function removeGroupPrivilege($group_name, $privilege_name): void
    {
        FcMigrationUtils::removeGroupPrivilege($this->db, $group_name, $privilege_name);
    }

    private function addGroupPrivilege($group_name, $privilege_name): void
    {
        FcMigrationUtils::addGroupPrivilege($this->db, $group_name, $privilege_name);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        throw new Exception('FC cannot be downgraded, sorry!');
    }
}


class FcMigrationUtils
{
    public static function renameGroup($db, $groupNameOld, $groupNameNew): void
    {
        $db->table('groups')->where('name', $groupNameOld)->update(['name' => $groupNameNew]);
    }

    public static function removeGroupPrivilege($db, $group_name, $privilege_name): void
    {
        $group = $db->table('groups')->where('name', $group_name)->first();
        if (!$group) {
            // No group found with that name.
            return;
        }

        $privilege = $db->table('privileges')->where('name', $privilege_name)->first();
        if(!$privilege) {
            // No privilege found with that name.
            return;
        }

        $db->table('group_privileges')->where('group_id', $group->id)->where('privilege_id', $privilege->id)->delete();
    }

    public static function addGroupPrivilege($db, $group_name, $privilege_name): void
    {
        $group = $db->table('groups')->where('name', $group_name)->first();
        if (!$group) {
            // No group found with that name.
            return;
        }

        $privilege = $db->table('privileges')->where('name', $privilege_name)->first();
        if(!$privilege) {
            // No privilege found with that name.
            return;
        }

        $group_privileges = $db->table('group_privileges')->where('group_id', $group->id)->where('privilege_id', $privilege->id);
        if($group_privileges->count() !== 0) {
            // GroupPrivilege already exists.
            return;
        }

        $db->table('group_privileges')->insert([
            ['group_id' => $group->id, 'privilege_id' => $privilege->id],
        ]);
    }
}
