@extends('layouts.layouts-landing')

@section('title', 'Home')

@section('content')
    <!-- Hero Section -->
    <div id="hero-section" class="hero-bg relative overflow-hidden bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="relative z-10 pb-8 sm:pb-16 md:pb-20 lg:pb-28 lg:w-full">
                <main class="mt-8 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8">
                    <div class="text-center">
                        <h1 class="text-3xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl pt-2">
                            <span class="block">Selamat Datang di</span>
                            <span class="block text-purple-600">Yourbucket MKS</span>
                        </h1>
                        <p class="mt-3 text-sm text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl">
                            Temukan produk berkualitas dengan harga fantastis.
                        </p>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Products Section -->
    <div id="featured-products" class="bg-white py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h2 class="text-2xl text-center sm:text-3xl font-bold text-gray-900 mb-4">Pencarian Products</h2>

                <form action="{{ route('home') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1 min-w-0">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search products..."
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200">
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="w-full sm:w-auto">
                            <select name="category"
                                class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full sm:w-auto">
                            <select name="sort"
                                class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200">
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest</option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            </select>
                        </div>
                    </div>

                    <div class="w-full sm:w-auto">
                        <button type="submit"
                            class="w-full px-6 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-2 gap-4 sm:gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse ($products as $product)
                <a href="/detail/{{ $product->slug }}">
                    <div class="product-card border bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300"
                        data-id="{{ $product->id }}"
                        data-name="{{ $product->name }}"
                        data-price="{{ $product->price }}"
                        data-image="{{ $product->getPrimaryImage() }}"
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

                            {{-- Harga + Stok --}}
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

            <!-- Pagination -->
            <div class="mt-6">
                <div class="flex items-center justify-center flex-wrap gap-2">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        {{ $products->links() }}
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <br><br>

    <!-- Newsletter Section -->
    <div id="newsletter-section" class="bg-purple-50 py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4">Subscribe to Our Newsletter</h2>
                <p class="text-sm sm:text-base text-gray-600 mb-6">Get the latest updates about our products and offers.</p>
                <form id="newsletter-form" class="max-w-md mx-auto">
                    @csrf
                    <div class="flex flex-col sm:flex-row gap-4">
                        <input type="email" name="email" placeholder="Enter your email"
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200">
                        <button type="submit"
                            class="w-full sm:w-auto px-6 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                            Subscribe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('js/app.js') }}"></script>
@endpush