<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

// The file setup_fc_group_permissions got us 90% of the way there for permissions
// But during FC 2025, we realized we would need to tweak things little bit further
// So this file further adjusts permissions to how we like them during a con.

class UpdateFcGroupPermissions extends Migration
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
        $this->upGofur();
        $this->upShiftCoordinator();
    }

    private function upGofur(): void
    {
        $group = 'Gofur';

        $this->addGroupPrivilege($group, 'admin_shifts');

        // The final set of privileges should be
        // admin_shifts, admin_user_worklog, angeltypes, atom, ical, locations.view, logout, news,
        // shifts_json_export, user_angeltypes, user_myshifts, user_settings, user_shifts
    }

    private function upShiftCoordinator(): void
    {
        $group = 'Shift Coordinator';

        $this->removeGroupPrivilege($group, 'admin_shifts');

        // The final set of privileges should be
        // admin_active, admin_arrive, admin_free, shifttypes.edit,
        // shifttypes.view, user.info.show, user_shifts_admin, users.arrive.list
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
