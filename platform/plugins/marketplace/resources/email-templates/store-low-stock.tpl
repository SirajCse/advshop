{{ header }}

<div class="bb-main-content">
    <table class="bb-box" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td class="bb-content bb-pb-0" align="center">
                    <table class="bb-icon bb-icon-lg bb-bg-yellow" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td valign="middle" align="center">
                                    <img src="{{ 'alert-triangle' | icon_url }}" class="bb-va-middle" width="40" height="40" alt="Icon" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <h1 class="bb-text-center bb-m-0 bb-mt-md">{{ 'plugins/marketplace::marketplace.email_templates.store_low_stock_title' | trans }}</h1>
                </td>
            </tr>
            <tr>
                <td class="bb-content">
                    {% if store_name %}
                        <div>{{ 'plugins/marketplace::marketplace.email_templates.dear_vendor' | trans({'vendor_name': store_name}) }}</div>
                    {% endif %}
                    <div>{{ 'plugins/marketplace::marketplace.email_templates.store_low_stock_message' | trans({'product_name': product_name, 'product_quantity': product_quantity, 'low_stock_threshold': low_stock_threshold}) }}</div>
                    <div>{{ 'plugins/marketplace::marketplace.email_templates.store_low_stock_restock' | trans }}</div>
                </td>
            </tr>
            {% if product_url %}
            <tr>
                <td class="bb-content" align="center">
                    <a href="{{ product_url }}" class="bb-btn bb-bg-blue">{{ 'plugins/marketplace::marketplace.email_templates.store_low_stock_view_product' | trans }}</a>
                </td>
            </tr>
            {% endif %}
        </tbody>
    </table>
</div>

{{ footer }}
