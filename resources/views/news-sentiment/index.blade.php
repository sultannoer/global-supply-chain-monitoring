@extends('layouts.app')

@section('content')
<div class="min-vh-100 text-white" style="background:#0b0f19;font-family:'Segoe UI',Roboto,sans-serif;">
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <a href="{{ route('ports.index') }}" class="back-dashboard"><i class="bi bi-arrow-left"></i>Kembali ke dashboard</a>
                <h2 class="fw-bold mb-1 mt-2"><i class="bi bi-newspaper text-info me-2"></i>News Sentiment Analysis</h2>
                <p class="text-white-50 mb-0 small">Klasifikasi artikel GNews menggunakan kamus kata positif dan negatif yang tersimpan di database.@if($country) Berita difilter untuk {{ $country->name }} dan menjadi sumber komponen berita Risk Score negara ini.@endif</p>
            </div>
            <a href="{{ url('/api/news') }}" target="_blank" class="btn btn-outline-info btn-sm"><i class="bi bi-code-slash me-1"></i>Lihat API Berita</a>
        </div>

        <form method="GET" action="{{ route('news-sentiment.index') }}" class="card bg-dark border-secondary border-opacity-25 shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-9">
                        <label for="sentimentCountry" class="form-label small text-white-50 mb-1">Cari berita berdasarkan negara</label>
                        <div id="sentimentCountryCombobox" class="position-relative">
                            <input id="sentimentCountry" name="country" value="{{ $country?->name }}" class="form-control form-control-sm bg-black border-secondary text-white" placeholder="Ketik nama negara atau kode ISO..." autocomplete="off">
                            <div id="sentimentCountryDropdown" class="d-none position-absolute top-100 start-0 w-100 bg-dark border border-secondary rounded shadow-lg" style="z-index: 20; max-height: 260px; overflow-y: auto;">
                                @foreach($countries as $item)
                                    <button type="button" class="sentiment-country-option btn btn-dark text-start w-100 rounded-0 border-0 px-3 py-2 small" data-name="{{ strtolower($item->name) }}" data-code="{{ $item->code }}">{{ $item->name }} <span class="text-white-50">({{ $item->code }})</span></button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 d-grid">
                        <button type="submit" class="btn btn-sm btn-info"><i class="bi bi-search me-1"></i>Tampilkan Berita</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="row g-3 mb-4">
            @foreach ([['Positive', 'positive', 'success'], ['Neutral', 'neutral', 'secondary'], ['Negative', 'negative', 'danger']] as [$label, $key, $color])
                <div class="col-12 col-md-4">
                    <div class="card h-100 bg-dark border-secondary border-opacity-25">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><small class="text-uppercase text-white-50">{{ $label }}</small><div class="fs-2 fw-bold text-{{ $color }}">{{ $summary[$key.'_percentage'] }}%</div><small class="text-white-50">{{ $summary[$key] }} artikel</small></div>
                            <i class="bi bi-{{ $key === 'positive' ? 'emoji-smile' : ($key === 'negative' ? 'emoji-frown' : 'dash-circle') }} fs-1 text-{{ $color }} opacity-75"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card bg-dark border-secondary border-opacity-25 shadow-sm">
                    <div class="card-header bg-transparent border-secondary border-opacity-25 d-flex justify-content-between"><span class="fw-semibold">Artikel GNews Terbaru</span><small class="text-white-50">{{ $summary['total_articles'] }} artikel dianalisis @if($country) untuk {{ $country->name }} @endif</small></div>
                    <div class="list-group list-group-flush">
                        @forelse ($articles as $article)
                            @php($color = $article['sentiment'] === 'Positive' ? 'success' : ($article['sentiment'] === 'Negative' ? 'danger' : 'secondary'))
                            <div class="list-group-item bg-dark text-white border-secondary border-opacity-25 p-3">
                                <div class="d-flex justify-content-between gap-3 align-items-start">
                                    <div class="flex-grow-1">
                                        <a href="{{ $article['url'] }}" target="_blank" rel="noopener" class="text-white fw-semibold text-decoration-none">{{ $article['title'] }}</a>
                                        @if (!empty($article['description']))<p class="small text-white-50 mb-2 mt-1">{{ $article['description'] }}</p>@endif
                                        <small class="text-white-50">{{ data_get($article, 'source.name', 'GNews') }} @if(!empty($article['publishedAt'])) · {{ \Carbon\Carbon::parse($article['publishedAt'])->diffForHumans() }} @endif</small>
                                    </div>
                                    <div class="text-end text-nowrap"><span class="badge text-bg-{{ $color }}">{{ $article['sentiment'] }}</span><small class="d-block text-white-50 mt-2">+{{ $article['positive_score'] }} / -{{ $article['negative_score'] }}</small></div>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item bg-dark text-white-50 text-center py-5">GNews belum mengembalikan artikel. Coba muat ulang beberapa saat lagi.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card bg-dark border-secondary border-opacity-25 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-secondary border-opacity-25"><span class="text-success fw-semibold">Kamus Positif ({{ $positiveWords->count() }})</span></div>
                    <div class="card-body"><div class="d-flex flex-wrap gap-2">@foreach($positiveWords as $word)<span class="badge rounded-pill text-bg-success">{{ $word }}</span>@endforeach</div></div>
                </div>
                <div class="card bg-dark border-secondary border-opacity-25 shadow-sm">
                    <div class="card-header bg-transparent border-secondary border-opacity-25"><span class="text-danger fw-semibold">Kamus Negatif ({{ $negativeWords->count() }})</span></div>
                    <div class="card-body"><div class="d-flex flex-wrap gap-2">@foreach($negativeWords as $word)<span class="badge rounded-pill text-bg-danger">{{ $word }}</span>@endforeach</div></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(() => {
    const search = document.getElementById('sentimentCountry');
    const dropdown = document.getElementById('sentimentCountryDropdown');
    const combobox = document.getElementById('sentimentCountryCombobox');
    const options = Array.from(document.querySelectorAll('.sentiment-country-option'));
    if (!search || !dropdown || !combobox) return;

    const filterCountries = (show = true) => {
        const query = search.value.trim().toLowerCase();
        let matches = 0;
        options.forEach((option) => {
            const match = query === '' || option.dataset.name.includes(query) || option.dataset.code.toLowerCase().includes(query);
            option.classList.toggle('d-none', !match);
            if (match) matches++;
        });
        dropdown.classList.toggle('d-none', !show || matches === 0);
    };

    search.addEventListener('focus', () => filterCountries(true));
    search.addEventListener('input', () => filterCountries(true));
    options.forEach((option) => option.addEventListener('click', () => {
        search.value = option.dataset.code;
        dropdown.classList.add('d-none');
    }));
    document.addEventListener('click', (event) => {
        if (!combobox.contains(event.target)) dropdown.classList.add('d-none');
    });
})();
</script>
@endsection
