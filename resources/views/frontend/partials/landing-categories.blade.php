<section class="category-one" style="background-image: url({{ asset('landing-page') }}/assets/images/shapes/category-bg-1.jpg);">
    <div class="container">
        <div class="section-title text-center">
            <h5 class="section-title__tagline">Kategori</h5>
            <h2 class="section-title__title">Topik Pembelajaran</h2>
        </div>
        @if (($categories ?? collect())->isNotEmpty())
            <div class="category-one__slider eduact-owl__carousel owl-with-shadow owl-theme owl-carousel" data-owl-options='{
            "items": 4,
            "margin": 30,
            "smartSpeed": 700,
            "loop":true,
            "autoplay": true,
            "nav":false,
            "dots":true,
            "responsive":{
                "0":{"items":1,"nav":true,"dots":false,"margin":0},
                "670":{"nav":true,"dots":false,"items":2},
                "992":{"items":3},
                "1200":{"items":3},
                "1400":{"items":4,"margin":36}
            }
            }'>
                @foreach ($categories as $category)
                    @include('frontend.partials.category-item', ['category' => $category, 'loop' => $loop])
                @endforeach
            </div>
        @else
            <p class="text-center text-muted mb-0">Kategori pelatihan akan tampil setelah data kursus tersedia.</p>
        @endif
    </div>
</section>
