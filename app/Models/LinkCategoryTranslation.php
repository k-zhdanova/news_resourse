<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Contracts\Activity;

class LinkCategoryTranslation extends Model
{

    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    protected static $logAttributes = [
        'name',
    ];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    /**
     * Disable timestamps
     *
     * @var array
     */
    public $timestamps = false;

    public function tapActivity(Activity $activity, string $eventName)
    {
        if ($this->isDirty('text')) {
            $properties = $activity->properties->toArray();
            $text = strip_tags($properties['attributes']['text']);
            $old_text = '';
            if (array_key_exists('old', $properties)) {
                $old_text = strip_tags($properties['old']['text']);
            }

            if (!strlen($old_text)) {
                $properties['attributes'] = array_merge($properties['attributes'], ['text' => $text]);
            } else {
                $opcodes = FineDiffHelper::getDiffOpcodes($old_text, $text, FineDiffHelper::$wordGranularity);
                $old_opcodes = FineDiffHelper::getDiffOpcodes($text, $old_text, FineDiffHelper::$wordGranularity);

                $opcodes = explode('ccc', $opcodes);
                $old_opcodes = explode('ccc', $old_opcodes);

                $phrases = [];
                $old_phrases = [];

                foreach ($opcodes as $optcode) {
                    if (strlen($optcode)) {
                        $pieces = \explode(':::', $optcode);
                        if (count($pieces) > 1) {
                            array_shift($pieces);
                            array_push($phrases, '...' . implode(':', $pieces) . '...');
                        }
                    }
                }

                foreach ($old_opcodes as $optcode) {
                    if (strlen($optcode)) {
                        $pieces = \explode(':::', $optcode);
                        if (count($pieces) > 1) {
                            array_shift($pieces);
                            array_push($old_phrases, '...' . implode(':', $pieces) . '...');
                        }
                    }
                }

                if (count($phrases) and count($old_phrases)) {
                    $properties['attributes'] = array_merge($properties['attributes'], ['text' => implode('<br>', $phrases)]);
                    $properties['old'] = array_merge($properties['old'], ['text' => implode('<br>', $old_phrases)]);
                }

            }
            $activity->properties = $properties;
        }
    }
}
