<?php

declare(strict_types=1);

namespace modules\users\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 */
final class User extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%users}}';
    }

    public function rules(): array
    {
        return [
            [['first_name', 'last_name'], 'required'],
            [['first_name', 'last_name'], 'string', 'max' => 300],
        ];
    }

    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
