<?php

namespace App\Models;

use App\Enums\TypeOfTestActionStatus;
use ArrayObject;
use BlueM\Tree;
use Carbon\Carbon;
use Hexbatch\Things\Interfaces\IThingAction;
use Hexbatch\Things\Interfaces\IThingOwner;
use Hexbatch\Things\Models\Thing;
use Hexbatch\Things\Models\ThingHook;
use Hexbatch\Things\Models\ThingSetting;
use Hexbatch\Things\Models\ThingStat;
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
 * @property TypeOfTestActionStatus action_status
 * @property string test_action_color
 * @property string test_action_type
 * @property string test_action_name
 * @property string parent_key
 * @property string test_action_run_class
 * @property string test_action_run_function
 *
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

    const ACTION_TYPE = 'tester';

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
        'test_action_color',
        'test_action_type',
        'test_action_name',
        'parent_key',
        'test_action_run_class',
        'test_action_run_function'
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
        ];
    }


    public function user_owner() : BelongsTo {
        return $this->belongsTo(User::class,'owner_user_id','id');
    }

    public function action_parent() : BelongsTo {
        return $this->belongsTo(TestActionDatum::class,'parent_action_id','id');
    }

    public function isActionComplete(): bool
    {
        return $this->action_status !== TypeOfTestActionStatus::ACTION_PENDING;
    }

    public function isActionError(): bool
    {
        return $this->action_status === TypeOfTestActionStatus::ACTION_ERROR;
    }

    public function isActionSuccess(): bool
    {
        return $this->action_status === TypeOfTestActionStatus::ACTION_SUCCESS;
    }

    public function isActionFail(): bool
    {
        return $this->action_status === TypeOfTestActionStatus::ACTION_FAIL;
    }

    public function getActionId(): int
    {
       return $this->id;
    }

    public function getActionRef(): string
    {
        return sprintf("%s %s #%s",$this->test_action_type,$this->test_action_name,$this->id);
    }

    public function getActionPriority(): int
    {
        return $this->test_action_priority;
    }

    public static function getActionType(): string
    {
        return static::ACTION_TYPE;
    }

    /**
     * @uses static::action_parent()
     * @param string|null $key
     * @return Tree
     */
    public function getChildrenTree(?string $key = null): Tree
    {
        //todo use sql to get the descendants using key
        $data = [
            ['id' => 1, 'parent' => 0, 'title' => 'Node 1'],
            ['id' => 2, 'parent' => 1, 'title' => 'Node 1.1'],
            ['id' => 3, 'parent' => 0, 'title' => 'Node 3'],
            ['id' => 4, 'parent' => 1, 'title' => 'Node 1.2'],
        ];
        return new Tree($data);
    }

    public function runAction(array $data = []): void
    {
        $this->test_action_content = array_merge($data,$this->test_action_content->getArrayCopy());

        if ($this->test_action_run_class && $this->test_action_run_function) {
            $method = "$this->test_action_run_class::$this->test_action_run_function";
        } elseif ($this->test_action_run_function) {
            $method = $this->test_action_run_function;
        } else {
            throw new \RuntimeException("no class or method, or only class, in callback for action");
        }
        try {
            //sets the status and updates the content
            call_user_func_array($method, [$this]);

        } catch (\Exception|\Error $e) {
            Log::warning("Got error when calling for action test $method :".$e->getMessage());
            $this->action_status = TypeOfTestActionStatus::ACTION_ERROR;
            throw $e;
        } finally {
            $this->save();
        }
    }

    public function getDataByteRowsUsed(): int
    {
        return  strlen(serialize($this->test_action_content->getArrayCopy()));
    }

    public function setLimitDataByteRows(int $limit): void
    {
        $this->test_action_data_row_limit = $limit;
        $this->save();
    }

    /**
     * @uses static::user_owner()
     * @return IThingOwner
     */
    public function getActionOwner(): ?IThingOwner
    {
        return $this->user_owner;
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

    public function isMoreBuilding(): ?string
    {
        //todo use sql to get any children of this with a key, return the first key
        return null;
    }

    public function getActionResult(): array
    {
        return $this->test_action_content->getArrayCopy();
    }

    public function getActionTags(): ?array
    {
        return $this->test_action_tags->getArrayCopy();
    }

    public function getRenderHtml(): ?string
    {
        //move to blade??
        return sprintf('<span style="color: %s">%s</span>',$this->test_action_color,$this->getActionRef());
    }

    public function getInitialConstantData(): ?array
    {
        return $this->test_action_constant->getArrayCopy();
    }

    public function setChildActionResult(IThingAction $child): void
    {
        $this->test_action_content = array_merge($child->getActionResult(),$this->getActionResult());
        $this->save();
    }

    public function addDataBeforeRun(array $data): void
    {
        $this->test_action_content = array_merge($data,$this->test_action_content->getArrayCopy());
        $this->save();
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
        ThingSetting::registerActionType(static::class);
        ThingStat::registerActionType(static::class);
    }


    public static function buildTestAction(
        ?int                  $id = null,
        ?bool                 $is_root = null,
        ?string               $test_action_type = null,
        ?string               $test_action_name = null,
        ?string               $parent_key = null,
        ?TypeOfTestActionStatus   $status = null
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

        if ($id) {
            $build->where('test_action_data.id',$id);
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

    public static function runBasicOrLogic(TestActionDatum $action) :TypeOfTestActionStatus {
        foreach ($action->test_action_content as $what) {
            if ($what) {return TypeOfTestActionStatus::ACTION_SUCCESS;}
        }
        return TypeOfTestActionStatus::ACTION_FAIL;
    }
}
