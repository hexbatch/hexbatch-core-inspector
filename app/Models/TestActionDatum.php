<?php

namespace App\Models;

use App\Enums\TypeOfTestActionStatus;
use App\Helpers\TestOwners\OwnerFromUser;
use App\Interfaces\ITestActionInnards;
use ArrayObject;
use BlueM\Tree;
use Carbon\Carbon;
use Hexbatch\Things\Interfaces\IThingAction;
use Hexbatch\Things\Interfaces\IThingOwner;
use Hexbatch\Things\Models\Thing;
use Hexbatch\Things\Models\ThingHook;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;


/**
 * @mixin Builder
 * @mixin \Illuminate\Database\Query\Builder
 * @property int id
 * @property int parent_action_id
 * @property int owner_user_id
 * @property int test_action_priority
 * @property int test_action_start_offset_seconds
 * @property int test_action_invalid_offset_seconds
 * @property int test_action_data_row_limit
 * @property bool test_action_async
 * @property ArrayObject test_action_content
 * @property ArrayObject test_action_constant
 * @property ArrayObject test_action_tags
 * @property ArrayObject test_action_innard_state
 * @property TypeOfTestActionStatus action_status
 * @property string test_action_color
 * @property string test_action_type
 * @property string test_action_name
 * @property string parent_key
 * @property string|ITestActionInnards test_action_innard_class
 * @property string created_at
 * @property string updated_at
 *
 * @property User user_owner
 * @property TestActionDatum|null action_parent
 *
 */
class TestActionDatum extends Model implements IThingAction
{
    protected $table = 'test_action_data';
    public $timestamps = false;

    const string ACTION_TYPE = 'tester';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_action_id',
        'test_action_priority',
        'test_action_start_offset_seconds',
        'test_action_invalid_offset_seconds',
        'test_action_data_row_limit',
        'test_action_async',
        'test_action_constant',
        'test_action_tags',
        'test_action_innard_state',
        'test_action_color',
        'test_action_type',
        'test_action_name',
        'parent_key',
        'test_action_innard_class',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action_status' => TypeOfTestActionStatus::class,
            'test_action_content' => AsArrayObject::class,
            'test_action_constant' => AsArrayObject::class,
            'test_action_tags' => AsArrayObject::class,
            'test_action_innard_state' => AsArrayObject::class,
        ];
    }


    public function user_owner() : BelongsTo {
        return $this->belongsTo(User::class,'owner_user_id','id');
    }

    public function action_parent() : BelongsTo {
        return $this->belongsTo(TestActionDatum::class,'parent_action_id','id')
            /** @uses static::action_parent() */
            ->with('action_parent');
    }


    public function getInnardClass() : ITestActionInnards|string {
        return $this->test_action_innard_class;
    }

    public function isActionComplete(): bool
    {
        return $this->action_status !== TypeOfTestActionStatus::ACTION_PENDING;
    }


    public function isActionSuccess(): bool
    {
        return $this->action_status === TypeOfTestActionStatus::ACTION_SUCCESS;
    }

    public function isActionFail(): bool
    {
        return $this->action_status === TypeOfTestActionStatus::ACTION_FAIL ||
            $this->action_status === TypeOfTestActionStatus::ACTION_ERROR;
    }

    public function isActionError(): bool
    {
        return $this->action_status === TypeOfTestActionStatus::ACTION_ERROR ;
    }

    public function getActionId(): int
    {
       return $this->id;
    }

    public function getActionRef(): ?string
    {
        try {
            return $this->getInnardClass()::getActionRefInnard(action: $this);
        } catch (\Exception|\Error $e) {
            Log::warning(sprintf("Got error when calling %s : %s ", $this->test_action_innard_class.'::getActionRefInnard',$e->getMessage()));
            $this->action_status = TypeOfTestActionStatus::ACTION_ERROR;
            throw $e;
        }
    }

    public function getActionPriority(): int
    {
        return $this->test_action_priority;
    }

    public function getActionType() : string {
        return static::getActionTypeStatic();
    }

    public static function getActionTypeStatic(): string
    {
        return static::ACTION_TYPE;
    }

    /**
     * @return Tree|null
     *@uses static::action_parent()
     */
    public function getChildrenTree(): ?Tree
    {
        try {
            return $this->getInnardClass()::getChildrenTreeInnard(action: $this);
        } catch (\Exception|\Error $e) {
            Log::warning(sprintf("Got error when calling %s : %s ", $this->test_action_innard_class.'::getChildrenTreeInnard',$e->getMessage()));
            $this->action_status = TypeOfTestActionStatus::ACTION_ERROR;
            throw $e;
        }
    }

    /** @return IThingAction[] */
    public function getMoreSiblingActions() : array {
        try {
            return $this->getInnardClass()::getMoreSiblingActionsInnard(action: $this);
        } catch (\Exception|\Error $e) {
            Log::warning(sprintf("Got error when calling %s : %s ", $this->test_action_innard_class.'::getChildrenTreeInnard',$e->getMessage()));
            $this->action_status = TypeOfTestActionStatus::ACTION_ERROR;
            throw $e;
        }
    }

    public function runAction(array $data = []): void
    {
        try {
            $this->getInnardClass()::runActionInnard(action: $this,data: $data);
        } catch (\Exception|\Error $e) {
            Log::warning(sprintf("Got error when calling %s : %s ", $this->test_action_innard_class.'::runActionInnard',$e->getMessage()));
            $this->action_status = TypeOfTestActionStatus::ACTION_ERROR;
            throw $e;
        } finally {
            $this->save();
        }
    }



    /**
     * @uses static::user_owner()
     * @return IThingOwner|null
     */
    public function getActionOwner(): ?IThingOwner
    {
        if (!$this->user_owner) {return null; }
        return new OwnerFromUser(user: $this->user_owner);
    }

    public function getStartAt(): ?Carbon
    {
        if (empty($this->test_action_start_offset_seconds)) {return null;}
        return Carbon::now('UTC')->addSeconds($this->test_action_start_offset_seconds);
    }

    public function getInvalidAt(): ?Carbon
    {
        if (empty($this->test_action_invalid_offset_seconds)) {return null;}
        return Carbon::now('UTC')->addSeconds($this->test_action_invalid_offset_seconds);
    }

    public function isAsync(): bool
    {
        return $this->test_action_async;
    }


    public function getActionResult(): array
    {
        try {
            return $this->getInnardClass()::getActionResultInnard(action: $this);
        } catch (\Exception|\Error $e) {
            Log::warning(sprintf("Got error when calling %s : %s ", $this->test_action_innard_class.'::getActionResultInnard',$e->getMessage()));
            $this->action_status = TypeOfTestActionStatus::ACTION_ERROR;
            throw $e;
        }
    }

    public function getPreRunData(): array
    {
        try {
            return $this->getInnardClass()::getPreRunDataInnard(action: $this);
        } catch (\Exception|\Error $e) {
            Log::warning(sprintf("Got error when calling %s : %s ", $this->test_action_innard_class.'::getPreRunDataInnard',$e->getMessage()));
            $this->action_status = TypeOfTestActionStatus::ACTION_ERROR;
            throw $e;
        }
    }

    public function getDataSnapshot(): array
    {
        try {
            return $this->getInnardClass()::getPreRunDataInnard(action: $this);
        } catch (\Exception|\Error $e) {
            Log::warning(sprintf("Got error when calling %s : %s ", $this->test_action_innard_class.'::getPreRunDataInnard',$e->getMessage()));
            $this->action_status = TypeOfTestActionStatus::ACTION_ERROR;
            throw $e;
        }
    }

    public function getActionTags(): ?array
    {
        return $this->test_action_tags->getArrayCopy();
    }

    public function getRenderHtml(): ?string
    {
        try {
            return $this->getInnardClass()::getRenderHtmlInnard(action: $this);
        } catch (\Exception|\Error $e) {
            Log::warning(sprintf("Got error when calling %s : %s ", $this->test_action_innard_class.'::getRenderHtmlInnard',$e->getMessage()));
            $this->action_status = TypeOfTestActionStatus::ACTION_ERROR;
            throw $e;
        }

    }

    public function getInitialConstantData(): ?array
    {
        return $this->test_action_constant?->getArrayCopy();
    }

    public function getIntFromConstants(string $key) : int {
        $const = $this->getInitialConstantData()??[];
        return $const[$key]??0;
    }

    public function getRootAction() : TestActionDatum {

        $it = $this;
        while($it->action_parent) {
            $it = $it->action_parent;
        }
        return $it;
    }

    public function setChildActionResult(IThingAction $child): void
    {
        try {
            $this->getInnardClass()::setChildActionResultInnard(action: $this,child: $child);
        } catch (\Exception|\Error $e) {
            Log::warning(sprintf("Got error when calling %s : %s ", $this->test_action_innard_class.'::setChildActionResultInnard',$e->getMessage()));
            $this->action_status = TypeOfTestActionStatus::ACTION_ERROR;
            throw $e;
        } finally {
            $this->save();
        }

    }

    public function addDataBeforeRun(array $data): void
    {
        try {
            $this->getInnardClass()::addDataBeforeRunInnard(action: $this,data: $data);
        } catch (\Exception|\Error $e) {
            Log::warning(sprintf("Got error when calling %s : %s ", $this->test_action_innard_class.'::addDataBeforeRunInnard',$e->getMessage()));
            $this->action_status = TypeOfTestActionStatus::ACTION_ERROR;
            throw $e;
        } finally {
            $this->save();
        }
    }

    public static function resolveAction(int $action_id): IThingAction
    {
        $ret = TestActionDatum::find($action_id);
        if (!$ret) {
            throw new \InvalidArgumentException("Action not found using $action_id");
        }
        return $ret;
    }

    public static function registerAction(): void
    {
        Thing::registerActionType(static::class);
        ThingHook::registerActionType(static::class);
    }


    public static function buildTestAction(
        ?int                    $me_id = null,
        ?bool                   $is_root = null,
        ?string                 $test_action_type = null,
        ?string                 $test_action_name = null,
        ?string                 $parent_key = null,
        ?TypeOfTestActionStatus $status = null
    )
    : Builder
    {

        /**
         * @var Builder $build
         */
        $build =  TestActionDatum::select('test_action_data.*')
            ->selectRaw(" extract(epoch from  test_action_data.created_at) as created_at_ts, ".
                " extract(epoch from  test_action_data.updated_at) as updated_at_ts")
        ;

        if ($me_id) {
            $build->where('test_action_data.id',$me_id);
        }

        if ($is_root !== null) {
            if ($is_root) {
                $build->whereNull('test_action_data.parent_action_id');
            } else {
                $build->whereNotNull('test_action_data.parent_action_id');
            }


        }

        if ($test_action_type) {
            $build->where('test_action_data.test_action_type',$test_action_type);
        }

        if ($test_action_name) {
            $build->where('test_action_data.test_action_name',$test_action_name);
        }

        if ($parent_key) {
            $build->where('test_action_data.parent_key',$parent_key);
        }

        if ($status) {
            $build->where('test_action_data.action_status',$status);
        }


        /** @uses static::action_parent(),static::user_owner() */
        $build->with('action_parent','user_owner');


        return $build;
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $ret = null;
        try {
            if ($field) {
                $ret = $this->where($field, $value)->first();
            } else {
                if (ctype_digit($value)) {
                    $ret = $this->where('id', $value)->first();
                } else {
                    $ret = $this->where('test_action_name', $value)->first();
                }
            }
            if ($ret) {
                $ret = static::buildTestAction(me_id:$ret->id)->first();
            }
        } finally {
            if (empty($ret)) {
                throw new \RuntimeException(
                   "Did not find test action with $field $value"
                );
            }
        }
        return $ret;

    }

}
