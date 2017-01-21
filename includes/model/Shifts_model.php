<?php

use Engelsystem\Database\DB;
use Engelsystem\ShiftsFilter;
use Engelsystem\ShiftSignupState;

/**
 * @param array $room
 * @return array
 */
function Shifts_by_room($room)
{
    $result = DB::select('SELECT * FROM `Shifts` WHERE `RID`=? ORDER BY `start`', [$room['RID']]);
    if (empty($result)) {
        engelsystem_error('Unable to load shifts.');
    }
    return $result;
}

/**
 * @param ShiftsFilter $shiftsFilter
 * @return array[]
 */
function Shifts_by_ShiftsFilter(ShiftsFilter $shiftsFilter)
{
    //@TODO
    $sql = 'SELECT * FROM (
      SELECT DISTINCT `Shifts`.*, `ShiftTypes`.`name`, `Room`.`Name` AS `room_name`
      FROM `Shifts`
      JOIN `Room` USING (`RID`)
      JOIN `ShiftTypes` ON `ShiftTypes`.`id` = `Shifts`.`shifttype_id`
      JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`
      WHERE `Shifts`.`RID` IN (' . implode(',', $shiftsFilter->getRooms()) . ')
      AND `start` BETWEEN ' . $shiftsFilter->getStartTime() . ' AND ' . $shiftsFilter->getEndTime() . '
      AND `NeededAngelTypes`.`angel_type_id` IN (' . implode(',', $shiftsFilter->getTypes()) . ')
      AND `NeededAngelTypes`.`count` > 0
      AND `Shifts`.`PSID` IS NULL
      
      UNION
      
      SELECT DISTINCT `Shifts`.*, `ShiftTypes`.`name`, `Room`.`Name` AS `room_name`
      FROM `Shifts`
      JOIN `Room` USING (`RID`)
      JOIN `ShiftTypes` ON `ShiftTypes`.`id` = `Shifts`.`shifttype_id`
      JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`room_id`=`Shifts`.`RID`
      WHERE `Shifts`.`RID` IN (' . implode(',', $shiftsFilter->getRooms()) . ')
      AND `start` BETWEEN ' . $shiftsFilter->getStartTime() . ' AND ' . $shiftsFilter->getEndTime() . '
      AND `NeededAngelTypes`.`angel_type_id` IN (' . implode(',', $shiftsFilter->getTypes()) . ')
      AND `NeededAngelTypes`.`count` > 0
      AND NOT `Shifts`.`PSID` IS NULL) AS tmp_shifts
          
      ORDER BY `start`';
    $result = DB::select($sql);
    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to load shifts by filter.');
    }
    return $result;
}

/**
 * @param ShiftsFilter $shiftsFilter
 * @return array[]
 */
function NeededAngeltypes_by_ShiftsFilter(ShiftsFilter $shiftsFilter)
{
    //@TODO
    $sql = '
      SELECT
          `NeededAngelTypes`.*,
          `Shifts`.`SID`,
          `AngelTypes`.`id`,
          `AngelTypes`.`name`,
          `AngelTypes`.`restricted`,
          `AngelTypes`.`no_self_signup`
      FROM `Shifts`
      JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`
      JOIN `AngelTypes` ON `AngelTypes`.`id`= `NeededAngelTypes`.`angel_type_id`
      WHERE `Shifts`.`RID` IN (' . implode(',', $shiftsFilter->getRooms()) . ')
      AND `start` BETWEEN ' . $shiftsFilter->getStartTime() . ' AND ' . $shiftsFilter->getEndTime() . '
      AND `Shifts`.`PSID` IS NULL

      UNION

      SELECT
            `NeededAngelTypes`.*,
            `Shifts`.`SID`,
            `AngelTypes`.`id`,
            `AngelTypes`.`name`,
            `AngelTypes`.`restricted`,
            `AngelTypes`.`no_self_signup`
      FROM `Shifts`
      JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`room_id`=`Shifts`.`RID`
      JOIN `AngelTypes` ON `AngelTypes`.`id`= `NeededAngelTypes`.`angel_type_id`
      WHERE `Shifts`.`RID` IN (' . implode(',', $shiftsFilter->getRooms()) . ')
      AND `start` BETWEEN ' . $shiftsFilter->getStartTime() . ' AND ' . $shiftsFilter->getEndTime() . '
      AND NOT `Shifts`.`PSID` IS NULL';
    $result = DB::select($sql);
    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to load needed angeltypes by filter.');
    }
    return $result;
}

/**
 * @param array $shift
 * @param array $angeltype
 * @return array|null
 */
function NeededAngeltype_by_Shift_and_Angeltype($shift, $angeltype)
{
    $result = DB::select('
          SELECT
              `NeededAngelTypes`.*,
              `Shifts`.`SID`,
              `AngelTypes`.`id`,
              `AngelTypes`.`name`,
              `AngelTypes`.`restricted`,
              `AngelTypes`.`no_self_signup`
          FROM `Shifts`
          JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`
          JOIN `AngelTypes` ON `AngelTypes`.`id`= `NeededAngelTypes`.`angel_type_id`
          WHERE `Shifts`.`SID`=?
          AND `AngelTypes`.`id`=?
          AND `Shifts`.`PSID` IS NULL
              
          UNION
              
          SELECT
                `NeededAngelTypes`.*,
                `Shifts`.`SID`,
                `AngelTypes`.`id`,
                `AngelTypes`.`name`,
                `AngelTypes`.`restricted`,
                `AngelTypes`.`no_self_signup`
          FROM `Shifts`
          JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`room_id`=`Shifts`.`RID`
          JOIN `AngelTypes` ON `AngelTypes`.`id`= `NeededAngelTypes`.`angel_type_id`
          WHERE `Shifts`.`SID`=?
          AND `AngelTypes`.`id`=?
          AND NOT `Shifts`.`PSID` IS NULL
      ',
        [
            $shift['SID'],
            $angeltype['id'],
            $shift['SID'],
            $angeltype['id']
        ]
    );
    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to load needed angeltypes by filter.');
    }
    if (empty($result)) {
        return null;
    }
    return $result[0];
}

/**
 * @param ShiftsFilter $shiftsFilter
 * @return array
 */
function ShiftEntries_by_ShiftsFilter(ShiftsFilter $shiftsFilter)
{
    // @TODO
    $sql = '
      SELECT
          `User`.`Nick`,
          `User`.`email`,
          `User`.`email_shiftinfo`,
          `User`.`Sprache`,
          `User`.`Gekommen`,
          `ShiftEntry`.`UID`,
          `ShiftEntry`.`TID`,
          `ShiftEntry`.`SID`,
          `ShiftEntry`.`Comment`,
          `ShiftEntry`.`freeloaded`
      FROM `Shifts`
      JOIN `ShiftEntry` ON `ShiftEntry`.`SID`=`Shifts`.`SID`
      JOIN `User` ON `ShiftEntry`.`UID`=`User`.`UID`
      WHERE `Shifts`.`RID` IN (' . implode(',', $shiftsFilter->getRooms()) . ')
      AND `start` BETWEEN ' . $shiftsFilter->getStartTime() . ' AND ' . $shiftsFilter->getEndTime() . '
      ORDER BY `Shifts`.`start`';
    $result = DB::select($sql);
    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to load shift entries by filter.');
    }
    return $result;
}

/**
 * Check if a shift collides with other shifts (in time).
 *
 * @param array $shift
 * @param array $shifts
 * @return bool
 */
function Shift_collides($shift, $shifts)
{
    foreach ($shifts as $other_shift) {
        if ($shift['SID'] != $other_shift['SID']) {
            if (!($shift['start'] >= $other_shift['end'] || $shift['end'] <= $other_shift['start'])) {
                return true;
            }
        }
    }
    return false;
}

/**
 * Returns the number of needed angels/free shift entries for an angeltype.
 *
 * @param array   $needed_angeltype
 * @param array[] $shift_entries
 * @return int
 */
function Shift_free_entries($needed_angeltype, $shift_entries)
{
    $taken = 0;
    foreach ($shift_entries as $shift_entry) {
        if ($shift_entry['freeloaded'] == 0) {
            $taken++;
        }
    }
    return max(0, $needed_angeltype['count'] - $taken);
}

/**
 * Check if shift signup is allowed from the end users point of view (no admin like privileges)
 *
 * @param array      $user
 * @param array      $shift       The shift
 * @param array      $angeltype   The angeltype to which the user wants to sign up
 * @param array|null $user_angeltype
 * @param array|null $user_shifts List of the users shifts
 * @param array      $needed_angeltype
 * @param array[]    $shift_entries
 * @return ShiftSignupState
 */
function Shift_signup_allowed_angel(
    $user,
    $shift,
    $angeltype,
    $user_angeltype,
    $user_shifts,
    $needed_angeltype,
    $shift_entries
) {
    $free_entries = Shift_free_entries($needed_angeltype, $shift_entries);

    if ($user['Gekommen'] == 0) {
        return new ShiftSignupState(ShiftSignupState::SHIFT_ENDED, $free_entries);
    }

    if ($user_shifts == null) {
        $user_shifts = Shifts_by_user($user);
    }

    $signed_up = false;
    foreach ($user_shifts as $user_shift) {
        if ($user_shift['SID'] == $shift['SID']) {
            $signed_up = true;
            break;
        }
    }

    if ($signed_up) {
        // you cannot join if you already singed up for this shift
        return new ShiftSignupState(ShiftSignupState::SIGNED_UP, $free_entries);
    }

    if (time() > $shift['start']) {
        // you can only join if the shift is in future
        return new ShiftSignupState(ShiftSignupState::SHIFT_ENDED, $free_entries);
    }
    if ($free_entries == 0) {
        // you cannot join if shift is full
        return new ShiftSignupState(ShiftSignupState::OCCUPIED, $free_entries);
    }

    if ($user_angeltype == null) {
        $user_angeltype = UserAngelType_by_User_and_AngelType($user, $angeltype);
    }

    if (
        $user_angeltype == null
        || ($angeltype['no_self_signup'] == 1 && $user_angeltype != null)
        || ($angeltype['restricted'] == 1 && $user_angeltype != null && !isset($user_angeltype['confirm_user_id']))
    ) {
        // you cannot join if user is not of this angel type
        // you cannot join if you are not confirmed
        // you cannot join if angeltype has no self signup

        return new ShiftSignupState(ShiftSignupState::ANGELTYPE, $free_entries);
    }

    if (Shift_collides($shift, $user_shifts)) {
        // you cannot join if user alread joined a parallel or this shift
        return new ShiftSignupState(ShiftSignupState::COLLIDES, $free_entries);
    }

    // Hooray, shift is free for you!
    return new ShiftSignupState(ShiftSignupState::FREE, $free_entries);
}

/**
 * Check if an angeltype supporter can sign up a user to a shift.
 *
 * @param array   $needed_angeltype
 * @param array[] $shift_entries
 * @return ShiftSignupState
 */
function Shift_signup_allowed_angeltype_supporter($needed_angeltype, $shift_entries)
{
    $free_entries = Shift_free_entries($needed_angeltype, $shift_entries);
    if ($free_entries == 0) {
        return new ShiftSignupState(ShiftSignupState::OCCUPIED, $free_entries);
    }

    return new ShiftSignupState(ShiftSignupState::FREE, $free_entries);
}

/**
 * Check if an admin can sign up a user to a shift.
 *
 * @param array   $needed_angeltype
 * @param array[] $shift_entries
 * @return ShiftSignupState
 */
function Shift_signup_allowed_admin($needed_angeltype, $shift_entries)
{
    $free_entries = Shift_free_entries($needed_angeltype, $shift_entries);

    if ($free_entries == 0) {
        // User shift admins may join anybody in every shift
        return new ShiftSignupState(ShiftSignupState::ADMIN, $free_entries);
    }

    return new ShiftSignupState(ShiftSignupState::FREE, $free_entries);
}

/**
 * Check if an angel can sign up for given shift.
 *
 * @param array      $signup_user
 * @param array      $shift       The shift
 * @param array      $angeltype   The angeltype to which the user wants to sign up
 * @param array|null $user_angeltype
 * @param array|null $user_shifts List of the users shifts
 * @param array      $needed_angeltype
 * @param array[]    $shift_entries
 * @return ShiftSignupState
 */
function Shift_signup_allowed(
    $signup_user,
    $shift,
    $angeltype,
    $user_angeltype = null,
    $user_shifts = null,
    $needed_angeltype,
    $shift_entries
) {
    global $user, $privileges;

    if (in_array('user_shifts_admin', $privileges)) {
        return Shift_signup_allowed_admin($needed_angeltype, $shift_entries);
    }

    if (
        in_array('shiftentry_edit_angeltype_supporter', $privileges)
        && User_is_AngelType_supporter($user, $angeltype)
    ) {
        return Shift_signup_allowed_angeltype_supporter($needed_angeltype, $shift_entries);
    }

    return Shift_signup_allowed_angel(
        $signup_user,
        $shift,
        $angeltype,
        $user_angeltype,
        $user_shifts,
        $needed_angeltype,
        $shift_entries
    );
}

/**
 * Delete a shift by its external id.
 *
 * @param int $shift_psid
 * @return bool
 */
function Shift_delete_by_psid($shift_psid)
{
    DB::delete('DELETE FROM `Shifts` WHERE `PSID`=?', [$shift_psid]);

    if (DB::getStm()->errorCode() != '00000') {
        return false;
    }

    return true;
}

/**
 * Delete a shift.
 *
 * @param int $shift_id
 * @return bool
 */
function Shift_delete($shift_id)
{
    mail_shift_delete(Shift($shift_id));

    $result = DB::delete('DELETE FROM `Shifts` WHERE `SID`=?', [$shift_id]);
    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to delete shift.');
    }
    return $result;
}

/**
 * Update a shift.
 *
 * @param array $shift
 * @return bool
 */
function Shift_update($shift)
{
    global $user;
    $shift['name'] = ShiftType($shift['shifttype_id'])['name'];
    mail_shift_change(Shift($shift['SID']), $shift);

    return (bool)DB::update('
      UPDATE `Shifts` SET
      `shifttype_id` = ?,
      `start` = ?,
      `end` = ?,
      `RID` = ?,
      `title` = ?,
      `URL` = ?,
      `PSID` = ?,
      `edited_by_user_id` = ?,
      `edited_at_timestamp` = ?
      WHERE `SID` = ?
    ',
        [
            $shift['shifttype_id'],
            $shift['start'],
            $shift['end'],
            $shift['RID'],
            $shift['title'],
            $shift['URL'],
            $shift['PSID'],
            $user['UID'],
            time(),
            $shift['SID']
        ]
    );
}

/**
 * Update a shift by its external id.
 *
 * @param array $shift
 * @return bool|null
 */
function Shift_update_by_psid($shift)
{
    $shift_source = DB::select('SELECT `SID` FROM `Shifts` WHERE `PSID`=?', [$shift['PSID']]);
    if (DB::getStm()->errorCode() != '00000') {
        return false;
    }

    if (empty($shift_source)) {
        return null;
    }

    $shift['SID'] = $shift_source[0]['SID'];
    return Shift_update($shift);
}

/**
 * Create a new shift.
 *
 * @param array $shift
 * @return int|false shift id or false
 */
function Shift_create($shift)
{
    global $user;
    DB::insert('
          INSERT INTO `Shifts` (
              `shifttype_id`,
              `start`,
              `end`,
              `RID`,
              `title`,
              `URL`,
              `PSID`,
              `created_by_user_id`,
              `created_at_timestamp`
          )
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ',
        [
            $shift['shifttype_id'],
            $shift['start'],
            $shift['end'],
            $shift['RID'],
            $shift['title'],
            $shift['URL'],
            $shift['PSID'],
            $user['UID'],
            time(),
        ]
    );
    if (DB::getStm()->errorCode() != '00000') {
        return false;
    }
    return DB::getPdo()->lastInsertId();
}

/**
 * Return users shifts.
 *
 * @param array $user
 * @param bool  $include_freeload_comments
 * @return array
 */
function Shifts_by_user($user, $include_freeload_comments = false)
{
    $result = DB::select('
          SELECT `ShiftTypes`.`id` AS `shifttype_id`, `ShiftTypes`.`name`,
          `ShiftEntry`.`id`, `ShiftEntry`.`SID`, `ShiftEntry`.`TID`, `ShiftEntry`.`UID`, `ShiftEntry`.`freeloaded`, `ShiftEntry`.`Comment`,
          ' . ($include_freeload_comments ? '`ShiftEntry`.`freeload_comment`, ' : '') . '
          `Shifts`.*, `Room`.* 
          FROM `ShiftEntry` 
          JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`) 
          JOIN `ShiftTypes` ON (`ShiftTypes`.`id` = `Shifts`.`shifttype_id`)
          JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) 
          WHERE `UID` = ? 
          ORDER BY `start`
      ',
        [
            $user['UID']
        ]
    );
    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to load users shifts.');
    }
    return $result;
}

/**
 * Returns Shift by id.
 *
 * @param int $shift_id Shift  ID
 * @return array|null
 */
function Shift($shift_id)
{
    $shifts_source = DB::select('
      SELECT `Shifts`.*, `ShiftTypes`.`name`
      FROM `Shifts` 
      JOIN `ShiftTypes` ON (`ShiftTypes`.`id` = `Shifts`.`shifttype_id`)
      WHERE `SID`=?', [$shift_id]);

    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to load shift.');
    }

    if (empty($shifts_source)) {
        return null;
    }

    $result = $shifts_source[0];

    $shiftsEntry_source = DB::select('
        SELECT `id`, `TID` , `UID` , `freeloaded`
        FROM `ShiftEntry`
        WHERE `SID`=?', [$shift_id]);

    $result['ShiftEntry'] = $shiftsEntry_source;
    $result['NeedAngels'] = [];

    $angelTypes = NeededAngelTypes_by_shift($shift_id);
    foreach ($angelTypes as $type) {
        $result['NeedAngels'][] = [
            'TID'        => $type['angel_type_id'],
            'count'      => $type['count'],
            'restricted' => $type['restricted'],
            'taken'      => $type['taken']
        ];
    }

    return $result;
}

/**
 * Returns all shifts with needed angeltypes and count of subscribed jobs.
 *
 * @return array|false
 */
function Shifts()
{
    $shifts_source = DB::select('
        SELECT `ShiftTypes`.`name`, `Shifts`.*, `Room`.`RID`, `Room`.`Name` AS `room_name` 
        FROM `Shifts`
        JOIN `ShiftTypes` ON (`ShiftTypes`.`id` = `Shifts`.`shifttype_id`)
        JOIN `Room` ON `Room`.`RID` = `Shifts`.`RID`
    ');

    if (DB::getStm()->errorCode() != '00000') {
        return false;
    }

    foreach ($shifts_source as &$shift) {
        $needed_angeltypes = NeededAngelTypes_by_shift($shift['SID']);
        $shift['angeltypes'] = $needed_angeltypes;
    }

    return $shifts_source;
}
