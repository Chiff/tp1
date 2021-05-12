<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Event
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property \Illuminate\Support\Carbon $registration_start
 * @property \Illuminate\Support\Carbon $registration_end
 * @property \Illuminate\Support\Carbon $event_start
 * @property \Illuminate\Support\Carbon|null $event_end
 * @property string $ext_id
 * @property int $min_teams
 * @property int $max_teams
 * @property int $min_team_members
 * @property int $max_team_members
 * @property int $user_id
 * @property-read \App\Models\User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $participants
 * @property-read int|null $participants_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Team[] $teams
 * @property-read int|null $teams_count
 * @method static \Illuminate\Database\Eloquent\Builder|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEventEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEventStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereExtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereMaxTeamMembers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereMaxTeams($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereMinTeamMembers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereMinTeams($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRegistrationEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRegistrationStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUserId($value)
 * @mixin \Eloquent
 */
	class IdeHelperEvent extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EventTeam
 *
 * @method static \Illuminate\Database\Eloquent\Builder|EventTeam newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTeam newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTeam query()
 * @mixin \Eloquent
 */
	class IdeHelperEventTeam extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Team
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property string $team_name
 * @property-read \App\Models\User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $team_members
 * @property-read int|null $team_members_count
 * @method static \Illuminate\Database\Eloquent\Builder|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereTeamName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereUserId($value)
 * @mixin \Eloquent
 */
	class IdeHelperTeam extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $firstname
 * @property string $surname
 * @property string $email
 * @property int $ext_id
 * @property string|null $encrypted_auth
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Event[] $ownEvents
 * @property-read int|null $own_events_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Team[] $ownTeams
 * @property-read int|null $own_teams_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Team[] $teams
 * @property-read int|null $teams_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEncryptedAuth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereExtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSurname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class IdeHelperUser extends \Eloquent implements \Illuminate\Contracts\Auth\Authenticatable, \Illuminate\Contracts\Auth\Access\Authorizable, \Tymon\JWTAuth\Contracts\JWTSubject {}
}

namespace App\Models{
/**
 * App\Models\UserEvent
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserEvent query()
 * @mixin \Eloquent
 */
	class IdeHelperUserEvent extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UserTeam
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserTeam newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTeam newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTeam query()
 * @mixin \Eloquent
 */
	class IdeHelperUserTeam extends \Eloquent {}
}
