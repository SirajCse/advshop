{{ header }}

<div class="bb-main-content">
    <table class="bb-box" cellpadding="0" cellspacing="0">
        <tbody>
        <tr>
            <td class="bb-content bb-pb-0" align="center">
                <table class="bb-icon bb-icon-lg bb-bg-blue" cellspacing="0" cellpadding="0">
                    <tbody>
                        <tr>
                            <td valign="middle" align="center">
                                <img src="{{ 'lock-open' | icon_url }}" class="bb-va-middle" width="40" height="40" alt="Icon" />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <h1 class="bb-text-center bb-m-0 bb-mt-md">{{ 'plugins/ecommerce::email-templates.digital_product_license_codes_title' | trans }}</h1>
            </td>
        </tr>
        <tr>
            <td class="bb-content">
                <p>{{ 'plugins/ecommerce::email-templates.digital_product_license_codes_greeting' | trans({'customer_name': customer_name}) }}</p>
                <div
                    style="
                        background-color: #f1f5f9;
                        border-radius: 8px;
                        padding: 16px;
                        margin-top: 16px;
                        color: #334155;
                        line-height: 1.6;
                    "
                >
                    <div style="font-weight: 600; color: #0f172a;">
                        {{ 'plugins/ecommerce::email-templates.digital_product_license_codes_thanks' | trans }}
                    </div>
                    <div style="margin-top: 8px;">
                        {{ 'plugins/ecommerce::email-templates.digital_product_license_codes_message' | trans }}
                    </div>
                </div>

                {% if order_id or payment_method or order_note %}
                    <div
                        style="
                            background-color: #f8fafc;
                            border: 1px solid #e2e8f0;
                            border-radius: 8px;
                            padding: 16px;
                            margin-top: 20px;
                        "
                    >
                        <table style="width: 100%; border-collapse: collapse;">
                            {% if order_id %}
                                <tr>
                                    <td
                                        style="
                                            padding: 8px 0;
                                            font-size: 12px;
                                            text-transform: uppercase;
                                            letter-spacing: 0.05em;
                                            color: #64748b;
                                        "
                                    >
                                        {{ 'plugins/ecommerce::email-templates.download_digital_products_order_number' | trans }}
                                    </td>
                                    <td
                                        style="
                                            padding: 8px 0;
                                            text-align: right;
                                            font-weight: 600;
                                            color: #0f172a;
                                        "
                                    >
                                        {{ order_id }}
                                    </td>
                                </tr>
                            {% endif %}

                            {% if payment_method %}
                                <tr>
                                    <td
                                        style="
                                            padding: 8px 0;
                                            font-size: 12px;
                                            text-transform: uppercase;
                                            letter-spacing: 0.05em;
                                            color: #64748b;
                                            border-top: 1px solid #e2e8f0;
                                        "
                                    >
                                        {{ 'plugins/ecommerce::email-templates.digital_product_license_codes_payment_method' | trans }}
                                    </td>
                                    <td
                                        style="
                                            padding: 8px 0;
                                            text-align: right;
                                            font-weight: 500;
                                            color: #334155;
                                            border-top: 1px solid #e2e8f0;
                                        "
                                    >
                                        {{ payment_method }}
                                    </td>
                                </tr>
                            {% endif %}

                            {% if order_note %}
                                <tr>
                                    <td
                                        colspan="2"
                                        style="
                                            padding-top: 12px;
                                            font-size: 14px;
                                            color: #475569;
                                            border-top: 1px solid #e2e8f0;
                                        "
                                    >
                                        <strong style="display: block; margin-bottom: 6px; color: #0f172a;">
                                            {{ 'plugins/ecommerce::email-templates.customer_new_order_note' | trans }}
                                        </strong>
                                        {{ order_note }}
                                    </td>
                                </tr>
                            {% endif %}
                        </table>
                    </div>
                {% endif %}
            </td>
        </tr>
        <tr>
            <td class="bb-content bb-pt-0">
                <h4>{{ 'plugins/ecommerce::email-templates.digital_product_license_codes_order_summary' | trans }}</h4>
                <div class="table">
                    <table style="width: 100%; border-collapse: collapse; margin-top: 8px;">
                        <thead>
                            <tr style="background-color: #0f172a; color: #ffffff;">
                                <th
                                    style="
                                        text-align: left;
                                        padding: 12px;
                                        font-size: 12px;
                                        letter-spacing: 0.05em;
                                        text-transform: uppercase;
                                    "
                                >
                                    {{ 'plugins/ecommerce::email-templates.digital_product_license_codes_product_image' | trans }}
                                </th>
                                <th
                                    style="
                                        text-align: left;
                                        padding: 12px;
                                        font-size: 12px;
                                        letter-spacing: 0.05em;
                                        text-transform: uppercase;
                                    "
                                >
                                    {{ 'plugins/ecommerce::email-templates.digital_product_license_codes_product_name' | trans }}
                                </th>
                                <th
                                    style="
                                        text-align: left;
                                        padding: 12px;
                                        font-size: 12px;
                                        letter-spacing: 0.05em;
                                        text-transform: uppercase;
                                    "
                                >
                                    {{ 'plugins/ecommerce::email-templates.digital_product_license_codes_license' | trans }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {% for product in digital_product_list %}
                                <tr
                                    style="
                                        border-bottom: 1px solid #e2e8f0;
                                        background-color: {{ loop.index is odd ? '#ffffff' : '#f8fafc' }};
                                    "
                                >
                                    <td style="padding: 14px 12px; vertical-align: top;">
                                        <img
                                            src="{{ product.product_image_url }}"
                                            alt="{{ product.product_name }}"
                                            width="56"
                                            style="
                                                border-radius: 6px;
                                                border: 1px solid #e2e8f0;
                                            "
                                        >
                                    </td>
                                    <td style="padding: 14px 12px; vertical-align: top; color: #0f172a;">
                                        <span style="font-weight: 600;">{{ product.product_name }}</span>

                                    {% if (product.product_options_text) %}
                                        <div style="margin-top: 6px; font-size: 13px; color: #64748b;">
                                            {{ product.product_options_text }}
                                        </div>
                                    {% endif %}
                                    </td>
                                    <td style="padding: 14px 12px; vertical-align: top;">
                                        {% if product.license_code %}
                                            <div
                                                style="
                                                    background-color: #f8fafc;
                                                    border: 1px dashed #94a3b8;
                                                    border-radius: 6px;
                                                    padding: 10px 14px;
                                                    font-family: 'Courier New', monospace;
                                                    font-size: 14px;
                                                    font-weight: 600;
                                                    color: #1e293b;
                                                "
                                            >
                                                {{ product.license_code }}
                                            </div>
                                        {% else %}
                                            <span style="color: #94a3b8;">{{ 'plugins/ecommerce::email-templates.digital_product_license_codes_na' | trans }}</span>
                                        {% endif %}
                                    </td>
                                </tr>
                            {% endfor %}
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
        <tr>
            <td class="bb-content bb-pt-0">
                <div
                    style="
                        background-color: #f8fafc;
                        border: 1px solid #e2e8f0;
                        border-radius: 8px;
                        padding: 18px;
                        margin-top: 24px;
                    "
                >
                    <h4 style="margin: 0 0 12px; color: #0f172a;">
                        {{ 'plugins/ecommerce::email-templates.digital_product_license_codes_important_notes' | trans }}
                    </h4>
                    <ul style="margin: 0; color: #475569; padding-left: 18px;">
                        <li style="margin-bottom: 6px;">
                            {{ 'plugins/ecommerce::email-templates.digital_product_license_codes_note_1' | trans }}
                        </li>
                        <li style="margin-bottom: 6px;">
                            {{ 'plugins/ecommerce::email-templates.digital_product_license_codes_note_2' | trans }}
                        </li>
                        <li style="margin-bottom: 6px;">
                            {{ 'plugins/ecommerce::email-templates.digital_product_license_codes_note_3' | trans }}
                        </li>
                        <li style="margin-bottom: 0;">
                            {{ 'plugins/ecommerce::email-templates.digital_product_license_codes_note_4' | trans }}
                        </li>
                    </ul>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</div>

{{ footer }}
