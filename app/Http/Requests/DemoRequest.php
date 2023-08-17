<?php

namespace App\Http\Requests;

use App\Annotation\Validate;

class DemoRequest extends AbstractRequest
{
    #[Validate(rules: ['required', 'integer'])]
    private int $id;

    #[Validate(rules: ['required'])]
    private string $name;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
