{{ header }}

<div class="bb-main-content">
    <table class="bb-box" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td class="bb-content">
                    <h3>{{ 'core/setting::setting.email.test_email_title' | trans }}</h3>
                    <p>{{ 'core/setting::setting.email.test_email_message' | trans({'site_title': site_title}) }}</p>
                    <p>{{ 'core/setting::setting.email.test_email_success_message' | trans }}</p>
                    <p><a href="{{ site_url }}">{{ site_url }}</a></p>
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{ footer }}
