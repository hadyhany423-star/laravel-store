<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

Route::get('/', function () {
    return redirect('/products');
});

Route::get('/support', function () {
    return view('support');
})->name('support');

Route::get('/categories', function () {
    $categories = Category::all();
    return view('categories', compact('categories'));
});

Route::middleware(['auth', 'seller'])->group(function () {
    Route::get('/categories/create', function () {
    $categories = Category::all();
    return view('create-category', compact('categories'));
    });

    Route::post('/categories', function (Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) ?: Str::random(12),
        ]);

        return redirect('/categories/create')->with('success', 'تم إضافة القسم بنجاح!');
    });

    Route::get('/categories/{id}/edit', function ($id) {
        $category = Category::findOrFail($id);

        return view('edit-category', compact('category'));
    });

    Route::post('/categories/{id}/update', function (Request $request, $id) {
        $category = Category::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$category->id,
        ]);

        $baseSlug = Str::slug($validated['name']) ?: 'category';
        $slug = $baseSlug;
        $suffix = 2;

        while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

        return redirect('/categories/create')->with('success', 'تم تعديل القسم بنجاح!');
    });

    Route::post('/categories/{id}/delete', function ($id) {
        $deleted = DB::transaction(function () use ($id) {
            $category = Category::whereKey($id)->lockForUpdate()->firstOrFail();

            if ($category->products()->exists()) {
                return false;
            }

            $category->delete();

            return true;
        });

        if (!$deleted) {
            return back()->withErrors([
                'category' => 'لا يمكن حذف قسم يحتوي على منتجات. احذف المنتجات أولاً.',
            ]);
        }

        return redirect('/categories/create')->with('success', 'تم حذف القسم بنجاح!');
    });
});

// عرض المنتجات الخاصة بالقسم
Route::get('/categories/{id}', function ($id) {
    $category = Category::findOrFail($id);
    return view('category-products', compact('category'));
});

Route::middleware(['auth', 'seller'])->group(function () {
    Route::get('/products/create', function () {
        $categories = Category::all();
        return view('create-product', compact('categories'));
    });

    Route::post('/products', function (Request $request) {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['slug'] = (Str::slug($validated['name']) ?: 'product').'-'.Str::lower(Str::random(8));
        $validated['image'] = $request->file('image')?->store('products', 'public');
        Product::create($validated);

        return redirect('/products')->with('success', 'تم إضافة المنتج بنجاح');
    });

    Route::get('/products/{id}/edit', function ($id) {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        return view('edit-product', compact('product', 'categories'));
    });

    Route::get('/products/{id}/images/edit', function ($id) {
        $product = Product::findOrFail($id);

        return view('edit-product-images', compact('product'));
    });

    Route::post('/products/{id}/images', function (Request $request, $id) {
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        $product = Product::findOrFail($id);
        $oldImage = $product->image;
        $newImage = $request->file('image')->store('products', 'public');

        if (!$newImage) {
            return back()->withErrors(['image' => 'تعذر حفظ الصورة، حاول مرة أخرى.']);
        }

        $product->update(['image' => $newImage]);

        if ($oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect('/products')->with('success', 'تم تحديث صورة المنتج بنجاح');
    });

    Route::post('/products/{id}/update', function (Request $request, $id) {
        $product = Product::findOrFail($id);
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['slug'] = (Str::slug($validated['name']) ?: 'product').'-'.Str::lower(Str::random(8));
        $oldImage = $product->image;
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }
        $product->update($validated);
        if ($request->hasFile('image') && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect('/products')->with('success', 'تم تعديل المنتج بنجاح');
    });

    Route::post('/products/{id}/delete', function ($id) {
        [$deleted, $image] = DB::transaction(function () use ($id) {
            $product = Product::whereKey($id)->lockForUpdate()->firstOrFail();

            if (OrderItem::where('product_id', $product->id)->exists()) {
                return [false, null];
            }

            $image = $product->image;
            $product->delete();

            return [true, $image];
        });

        if (!$deleted) {
            return back()->withErrors([
                'product' => 'لا يمكن حذف منتج مرتبط بطلب سابق حتى لا يُحذف من سجل المشتريات.',
            ]);
        }

        if ($image && Storage::disk('public')->exists($image)) {
            Storage::disk('public')->delete($image);
        }

        return redirect('/products')->with('success', 'تم حذف المنتج بنجاح');
    });
});

Route::get('/products/{id}', function ($id) {
    $product = Product::findOrFail($id);
    return view('product-detail', compact('product'));
});

// صفحة حساب المستخدم وعرض سجل الطلبات
Route::get('/profile', function () {
    $user = Auth::user();
    $orders = Order::where('user_id', $user->id)->with('items.product')->get();
    $addresses = $user->addresses;

    return view('profile', compact('user', 'orders', 'addresses'));
})->middleware('auth');

// عرض صفحة التسجيل
Route::get('/register', function () {
    return view('register');
});

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email_or_phone' => 'required|email|max:255|unique:users,email',
        'password' => 'required|string|min:8',
        'age' => 'nullable|integer|min:1|max:120',
        'gender' => 'nullable|in:male,female',
    ]);

    User::create([
        'name' => $validated['name'],
        'email' => $validated['email_or_phone'],
        'password' => Hash::make($validated['password']),
        'role' => 'buyer',
        'age' => $validated['age'] ?? null,
        'gender' => $validated['gender'] ?? null,
    ]);

    return redirect('/login');
});

// عرض صفحة تسجيل الدخول
Route::get('/login', function () {
    return view('login');
})->name('login');

// استقبال بيانات تسجيل الدخول
Route::post('/login', function (Request $request) {
    $credentials = [
        'email'    => $request->email_or_phone,
        'password' => $request->password,
    ];

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/products');
    }

    return back()->withErrors([
        'email' => 'بيانات الدخول غير صحيحة.',
    ]);
});

// تسجيل الخروج
Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
});

// إضافة منتج للسلة
Route::post('/cart/add/{id}', function ($id, Request $request) {
    $cart = session()->get('cart', []);
    $available = true;

    DB::transaction(function () use ($id, &$cart, &$available) {
        $product = Product::whereKey($id)->lockForUpdate()->firstOrFail();
        $currentQuantity = (int) ($cart[$id]['quantity'] ?? 0);
        $reserved = (bool) ($cart[$id]['reserved'] ?? false);
        $quantityToReserve = $reserved ? 1 : $currentQuantity + 1;

        if ($product->stock < $quantityToReserve) {
            $available = false;
            return;
        }

        $product->decrement('stock', $quantityToReserve);

        $cart[$id] = [
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $currentQuantity + 1,
            'image' => $product->image,
            'reserved' => true,
        ];
    });

    if (!$available) {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => 'الكمية غير متاحة.'], 422);
        }

        return back()->withErrors(['cart' => 'الكمية غير متاحة.']);
    }

    session()->put('cart', $cart);

    // حساب إجمالي القطع في السلة
    $cartCount = 0;
    foreach ($cart as $details) {
        $cartCount += $details['quantity'];
    }

    // لو الطلب جاي بـ AJAX، رجع الرد JSON فيه العدد الجديد
    if ($request->expectsJson() || $request->ajax()) {
        return response()->json([
            'success' => true,
            'cartCount' => $cartCount,
            'message' => 'تم إضافة المنتج إلى السلة بنجاح!'
        ]);
    }

    return redirect()->back()->with('success', 'تم إضافة المنتج إلى السلة بنجاح!');
});

// صفحة عرض السلة
Route::get('/cart', function () {
    $cart = session()->get('cart', []);
    return view('cart', compact('cart'));
})->name('cart.index');

// إفراغ السلة
Route::post('/cart/clear', function () {
    $cart = session()->get('cart', []);

    DB::transaction(function () use ($cart) {
        foreach ($cart as $id => $details) {
            $quantity = (int) ($details['quantity'] ?? 0);
            if ($quantity < 1 || !($details['reserved'] ?? false)) {
                continue;
            }

            $product = Product::whereKey($id)->lockForUpdate()->first();
            $product?->increment('stock', $quantity);
        }
    });

    session()->forget('cart');
    return redirect()->back()->with('success', 'تم إفراغ السلة بالكامل!');
});

//العنواين للبايع والمشتري
Route::get('/addresses', function () {
    $user = Auth::user();
    $addresses = $user->addresses;

    return view('addresses', compact('addresses'));
})->middleware('auth');

// حفظ عنوان جديد
Route::post('/addresses', function (Request $request) {
    $validated = $request->validate([
        'address' => 'required|string|max:255',
        'city' => 'required|string|max:255',
    ]);

    Address::create([
        'user_id' => Auth::id(),
        'address' => $validated['address'],
        'city' => $validated['city'],
    ]);

    return redirect('/addresses')->with('success', 'تم إضافة العنوان بنجاح!');
})->middleware('auth');

// حذف العنوان
Route::post('/addresses/{id}/delete', function ($id) {
    $address = Address::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
    $address->delete();

    return redirect('/addresses')->with('success', 'تم حذف العنوان بنجاح!');
})->middleware('auth');

// التحكم في سلة  وإتمام الطلب
Route::post('/checkout', function (Request $request) {
    $validated = $request->validate([
        'address' => 'required|string|max:255',
    ]);
    $cart = session()->get('cart', []);

    if (empty($cart)) {
        return back()->withErrors(['cart' => 'سلة المشتريات فارغة.']);
    }

    DB::transaction(function () use ($cart, $validated) {
        $products = [];
        $totalCents = 0;

        foreach ($cart as $id => $details) {
            $quantity = filter_var($details['quantity'] ?? null, FILTER_VALIDATE_INT);
            if (!$quantity || $quantity < 1) {
                throw ValidationException::withMessages(['cart' => 'كمية أحد المنتجات غير صالحة.']);
            }

            $product = Product::whereKey($id)->lockForUpdate()->first();
            $reserved = (bool) ($details['reserved'] ?? false);
            if (!$product || (!$reserved && $quantity > $product->stock)) {
                throw ValidationException::withMessages(['cart' => 'الكمية المطلوبة غير متاحة في المخزون.']);
            }

            $products[] = [$product, $quantity, $reserved];
            $totalCents += (int) round((float) $product->price * 100) * $quantity;
        }

        $order = Order::create([
            'user_id' => Auth::id(),
            'total_price' => $totalCents / 100,
            'status' => 'pending',
            'address' => $validated['address'],
        ]);

        foreach ($products as [$product, $quantity, $reserved]) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price,
            ]);
            if (!$reserved) {
                $product->decrement('stock', $quantity);
            }
        }
    });

    // تفريغ السلة
    session()->forget('cart');

    return redirect('/profile');
})->middleware('auth')->name('checkout.store');

// إلغاء الطلب للمشتري
Route::post('/orders/{id}/cancel', function ($id) {
    DB::transaction(function () use ($id) {
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('items')
            ->lockForUpdate()
            ->firstOrFail();

        if (!in_array($order->status, ['قيد المعالجة', 'pending'], true)) {
            throw ValidationException::withMessages(['order' => 'لا يمكن إلغاء هذا الطلب.']);
        }

        foreach ($order->items as $item) {
            $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
            $product?->increment('stock', $item->quantity);
        }

        $order->update(['status' => 'cancelled']);
    });

    return redirect('/profile')->with('success', 'تم إلغاء الطلب بنجاح.');
})->middleware('auth');

// حذف منتج  من السلة
Route::post('/cart/remove/{id}', function ($id) {
    $cart = session()->get('cart', []);

    if (isset($cart[$id])) {
        $details = $cart[$id];
        $quantity = (int) ($details['quantity'] ?? 0);

        if ($quantity > 0 && ($details['reserved'] ?? false)) {
            DB::transaction(function () use ($id, $quantity) {
                $product = Product::whereKey($id)->lockForUpdate()->first();
                $product?->increment('stock', $quantity);
            });
        }

        unset($cart[$id]);
        session()->put('cart', $cart);
    }

    return redirect()->back()->with('success', 'تم حذف المنتج من السلة بنجاح!');
});

// عرض المنتجات
Route::get('/products', function (Request $request) {
    $search = $request->input('search');

    if ($search) {
        $products = Product::where('name', 'LIKE', '%' . $search . '%')->get();
    } else {
        $products = Product::all();
    }

    return view('products', compact('products'));
});

// تحديث كمية منتجات في السلة
Route::post('/cart/update/{id}', function ($id, Request $request) {
    $cart = session()->get('cart', []);

    if (isset($cart[$id])) {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        $newQuantity = $validated['quantity'];
        $oldQuantity = (int) ($cart[$id]['quantity'] ?? 0);
        $wasReserved = (bool) ($cart[$id]['reserved'] ?? false);
        $available = true;

        DB::transaction(function () use ($id, $newQuantity, $oldQuantity, $wasReserved, &$cart, &$available) {
            $product = Product::whereKey($id)->lockForUpdate()->firstOrFail();
            $quantityDelta = $wasReserved ? $newQuantity - $oldQuantity : $newQuantity;

            if ($quantityDelta > $product->stock) {
                $available = false;
                return;
            }

            if ($quantityDelta > 0) {
                $product->decrement('stock', $quantityDelta);
            } elseif ($quantityDelta < 0) {
                $product->increment('stock', abs($quantityDelta));
            }

            $cart[$id]['quantity'] = $newQuantity;
            $cart[$id]['reserved'] = true;
        });

        if (!$available) {
            return back()->withErrors(['quantity' => 'الكمية المطلوبة غير متاحة في المخزون.']);
        }

        session()->put('cart', $cart);
    }

    return redirect()->back()->with('success', 'تم تحديث الكمية بنجاح!');
});
