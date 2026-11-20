@props(['items' => []])
@forelse ($items as $i)
    <span class="pill pill-merah mb-1 mr-1" title="{{ $i['kode'] }}">{{ $i['label'] }}</span>
@empty
    <span class="pill pill-hijau">Normal</span>
@endforelse
