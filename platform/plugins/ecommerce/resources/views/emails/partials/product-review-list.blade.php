<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
    @foreach ($products as $product)
        <tr style="border-bottom: 1px solid #e5e7eb;">
            <td style="padding: 16px 8px; vertical-align: middle;">
                <div style="font-size: 15px; font-weight: 500; color: #111827; margin-bottom: 8px;">
                    {{ $product->name }}
                </div>
                <a href="{{ route('public.product.review', $product->slug) }}" style="font-size: 13px; color: #2563eb; text-decoration: none;">
                    {{ trans('plugins/ecommerce::review.write_review_button') }} →
                </a>
            </td>
        </tr>
    @endforeach
</table>
