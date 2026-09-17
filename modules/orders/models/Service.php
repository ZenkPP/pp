<?php

declare(strict_types=1);

namespace modules\orders\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 */
final class Service extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%services}}';
    }

    /**
     * @return array<array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 300],
        ];
    }
}
