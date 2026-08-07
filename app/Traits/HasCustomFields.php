<?php

namespace App\Traits;

use App\Models\CustomFieldValue;
use App\Models\Fieldset;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCustomFields
{
    public function customFieldValues(): MorphMany
    {
        return $this->morphMany(CustomFieldValue::class, 'valuable');
    }

    public function getCustomFieldValue(int $customFieldId): ?string
    {
        return $this->customFieldValues
            ->where('custom_field_id', $customFieldId)
            ->first()?->value;
    }

    public function syncCustomFields(array $fields): void
    {
        foreach ($fields as $fieldId => $value) {
            if ($value !== null && $value !== '') {
                $this->customFieldValues()->updateOrCreate(
                    ['custom_field_id' => $fieldId],
                    ['value' => $value],
                );
            } else {
                $this->customFieldValues()->where('custom_field_id', $fieldId)->delete();
            }
        }
    }

    /**
     * Get the fieldsets for this model through its category.
     */
    public function getFieldsetsAttribute()
    {
        if (!method_exists($this, 'category') || !$this->category) {
            return collect();
        }

        return $this->category->fieldsets()->with('customFields')->get();
    }
}
