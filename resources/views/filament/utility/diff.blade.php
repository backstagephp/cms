<div class="grid grid-cols-2 gap-4">
    <div class="flex flex-col">
        <div class="text-red-500 font-medium mb-2 p-2">{{ $name }} (Current)</div>
        <div class="text-red-800 bg-red-50 rounded p-2 break-all whitespace-pre-wrap">{!! $original !!}</div>
    </div>
    <div class="flex flex-col">
        <div class="text-green-500 font-medium mb-2 p-2">{{ $name }} (Revision)</div>
        <div class="text-green-800 bg-green-50 rounded p-2 break-all whitespace-pre-wrap">{!! $new !!}</div>
    </div>
</div>