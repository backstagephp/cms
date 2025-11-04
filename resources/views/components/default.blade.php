<div {{ $attributes }}>
    @if (config('app.debug'))
        <div class="border border-black p-4 bg-white text-black">
            This is the default component when no component is found. See <a href="https://github.com/vormkracht10/backstage/tree/main/docs" target="_blank">documentation</a>.<br />
            If this block doesn't have any parameters add the following view.<br />
<?php
echo Illuminate\Support\Str::markdown('```php
// resources/views/components/blocks/'. $_type .'.blade.php
<div>
    This is my component.
</div>
```');
?>

            If this block requires parameters add the following files.<br />
<?php
echo Illuminate\Support\Str::markdown("
```php
<?php
// app/View/Components/" . \Illuminate\Support\Str::studly($_type) .".php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ANewAwesome extends Component
{

    public function __construct(public string \$text = '', public string \$description = '')
    {
        //
    }

    public function render(): View|Closure|string
    {
        return view('components.a-new-awesome');
    }
}
```");
?>
        </div>
    @endif
</div>