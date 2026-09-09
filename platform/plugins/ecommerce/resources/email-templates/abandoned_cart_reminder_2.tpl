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
                                <img src="{{ 'bell' | icon_url }}" class="bb-va-middle" width="40" height="40" alt="Icon" />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <h1 class="bb-text-center bb-m-0 bb-mt-md">{{ 'plugins/ecommerce::email-templates.abandoned_cart_reminder_2_title' | trans }}</h1>
            </td>
        </tr>
        <tr>
            <td class="bb-content">
                <p>{{ 'plugins/ecommerce::email-templates.abandoned_cart_reminder_2_greeting' | trans({'customer_name': customer_name}) }}</p>
                <div>{{ 'plugins/ecommerce::email-templates.abandoned_cart_reminder_2_message' | trans }}</div>
            </td>
        </tr>
        <tr>
            <td class="bb-content bb-pt-0">
                <h4>{{ 'plugins/ecommerce::email-templates.abandoned_cart_items_title' | trans }}</h4>
                {{ product_list }}
                <p class="bb-mt-md"><strong>{{ 'plugins/ecommerce::email-templates.abandoned_cart_total' | trans }}:</strong> {{ cart_total }}</p>
            </td>
        </tr>
        <tr>
            <td class="bb-content bb-text-center bb-pt-0 bb-pb-xl">
                <table cellspacing="0" cellpadding="0">
                    <tbody>
                    <tr>
                        <td align="center">
                            <table cellpadding="0" cellspacing="0" border="0" class="bb-bg-blue bb-rounded bb-w-auto">
                                <tbody>
                                    <tr>
                                        <td align="center" valign="top" class="lh-1">
                                            <a href="{{ cart_recover_url }}" class="bb-btn bb-bg-blue bb-border-blue">
                                                <span class="btn-span">{{ 'plugins/ecommerce::email-templates.abandoned_cart_button' | trans }}</span>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td class="bb-content bb-pt-0 bb-text-center">
                <p style="color:#666;font-size:12px;">{{ 'plugins/ecommerce::email-templates.abandoned_cart_unsubscribe' | trans({'unsubscribe_url': unsubscribe_url}) }}</p>
            </td>
        </tr>
        </tbody>
    </table>
</div>

{{ footer }}
