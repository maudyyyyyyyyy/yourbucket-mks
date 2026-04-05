@extends('layouts.layouts-landing')

@section('title', 'details')

@section('content')

    <main class="container mx-auto mt-10 px-6 max-w-7xl">
        <div class="product-card flex flex-col md:flex-row" data-id="{{ $query->id }}" data-name="{{ $query->name }}"
            data-price="{{ $query->price }}" data-image="{{ $query->getPrimaryImage() }}"
            data-category="{{ $query->category->name }}">
            <div class="md:w-1/2">
                <img alt="{{ $query->name }}" class="w-full h-96 object-cover rounded-lg border shadow-lg" height="400"
                    src="{{ $query->getPrimaryImage() }}" width="600" />
            </div>
            <div class="md:w-1/2 md:pl-10 mt-6 md:mt-0">
                <h1 class="text-3xl font-bold">
                    {{ $query->name }}
                </h1>
                <p class="mt-4 text-purple-600 lg:text-4xl text-3xl font-bold">
                    {{ $query->formatted_price }}
                </p>

                {{-- ✅ Stok produk --}}
                <div class="mt-3">
                    @if ($query->stock <= 0)
                        <span class="text-sm font-semibold text-red-600">🚫 Habis</span>
                    @elseif ($query->stock <= 5)
                        <span class="text-sm font-semibold text-yellow-600">⚠️ Sisa {{ $query->stock }} item</span>
                    @else
                        <span class="text-sm font-semibold text-green-600">✅ Stok {{ $query->stock }} item</span>
                    @endif
                </div>

                <div class="mt-4">
                    <span class="text-yellow-500">
                        <i class="text-yellow-500 fa fa-star"></i>
                        <i class="text-yellow-500 fa fa-star"></i>
                        <i class="text-yellow-500 fa fa-star"></i>
                        <i class="text-yellow-500 fa fa-star"></i>
                        <i class="text-yellow-500 fa fa-star-half-alt"></i>
                    </span>
                    <span class="ml-2 text-gray-600">
                        (120 reviews)
                    </span>
                </div>

                {{-- ✅ Disable tombol jika stok habis --}}
                @if ($query->stock <= 0)
                    <button disabled
                        class="px-4 py-2 sm:px-4 sm:py-2 text-sm mt-4 bg-gray-300 text-white rounded-lg cursor-not-allowed">
                        <i class="fa fa-shopping-cart mr-2"></i>
                        Stok Habis
                    </button>
                @else
                    <button class="add-to-cart px-4 py-2 sm:px-4 sm:py-2 text-sm mt-4 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                        <i class="fa fa-shopping-cart mr-2"></i>
                        Tambah Keranjang
                    </button>
                @endif
            </div>
        </div>

        <section class="mt-16">
            <h2 class="text-2xl font-bold">
                Deskripsi Produk
            </h2>
            <p class="mt-4 text-gray-600">
                {!! nl2br($query->description) !!}
            </p>
        </section>

        <section class="mt-16">
            <h2 class="text-2xl font-bold mb-10 text-center">
                PRODUK LAIN YANG SERUPA
            </h2>
            <div class="grid grid-cols-2 gap-4 sm:gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse ($products as $product)
                    <a href="/detail/{{ $product->slug }}">
                        <div class="product-card bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300"
                            data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                            data-price="{{ $product->price }}" data-image="{{ $product->getPrimaryImage() }}"
                            data-category="{{ $product->category->name }}">
                            <img src="{{ $product->getPrimaryImage() }}" alt="{{ $product->name }}"
                                class="w-full h-48 sm:h-72 object-cover">
                            <div class="p-3 sm:p-4">
                                <h3 class="product-name text-base sm:text-lg font-medium text-gray-900">
                                    {{ $product->name }}
                                </h3>
                                <p class="product-description mt-1 text-xs sm:text-sm text-gray-500">
                                    {{ Str::limit($product->description, 100) }}
                                </p>
                                <div class="mt-4 flex items-center justify-between">
                                    <p class="text-base sm:text-lg font-semibold text-purple-600">
                                        {{ $product->formatted_price }}
                                    </p>

                                    {{-- ✅ Stok di samping harga --}}
                                    @if ($product->stock <= 0)
                                        <span class="text-xs font-semibold text-red-600">🚫 Habis</span>
                                    @elseif ($product->stock <= 5)
                                        <span class="text-xs font-semibold text-yellow-600">⚠️ Sisa {{ $product->stock }}</span>
                                    @else
                                        <span class="text-xs font-semibold text-green-600">✅ Stok {{ $product->stock }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full text-center py-8 sm:py-12">
                        <h3 class="text-base sm:text-lg font-medium text-gray-900">No products found</h3>
                        <p class="mt-2 text-sm text-gray-500">Try adjusting your search or filter</p>
                    </div>
                @endforelse
            </div>
        </section>
    </main>

@endsection

@push('scripts')
    <script type="module" src="{{ asset('js/app.js') }}"></script>
@endpush