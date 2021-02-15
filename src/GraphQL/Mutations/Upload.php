<?php

namespace GenesysLite\GenesysInventory\GraphQL\Mutations;


class Upload
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        $file = $args['file'];

        return [
            'file_url' => $file->storePublicly('products', 'public')
        ];
    }
}
