<!DOCTYPE html>
<html {{ html_attributes }}>

<head>
    <meta charset="UTF-8">
    <title>{{ site_title }}</title>
</head>

<body class="bb-bg-body" {{ body_attributes }}>

<center>
    <table class="bb-main bb-bg-body" width="100%" cellspacing="0" cellpadding="0">
        <tbody>
            <tr>
                <td align="center" valign="top">
                    <table class="bb-wrap" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td class="bb-p-sm">
                                    <table cellpadding="0" cellspacing="0">
                                        <tbody>
                                            <tr>
                                                <td class="bb-py-lg">
                                                    <table cellspacing="0" cellpadding="0">
                                                        <tbody>
                                                            <tr>
                                                                <td class="bb-text-left">
                                                                    <a href="{{ site_url }}">
                                                                        <img class="bb-logo" src="{{ site_logo }}" alt="{{ site_title }}"{% if logo_width and logo_height %} width="{{ logo_width }}" height="{{ logo_height }}"{% else %} height="{{ max_height_for_logo }}"{% endif %} style="display: block; max-height: {{ max_height_for_logo }}px; width: auto; max-width: 100%;" />
                                                                    </a>
                                                                </td>
                                                                <td class="bb-text-right">
                                                                    {{ date_time }}
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
