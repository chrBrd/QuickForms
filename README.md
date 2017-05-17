[![Build Status](https://travis-ci.org/chrBrd/QuickForms.svg?branch=master)](https://travis-ci.org/chrBrd/QuickForms)
[![Test Coverage](https://codeclimate.com/github/chrBrd/QuickForms/badges/coverage.svg)](https://codeclimate.com/github/chrBrd/QuickForms/coverage)
[![Code Climate](https://codeclimate.com/github/chrBrd/QuickForms/badges/gpa.svg)](https://codeclimate.com/github/chrBrd/QuickForms)
[![Issue Count](https://codeclimate.com/github/chrBrd/QuickForms/badges/issue_count.svg)](https://codeclimate.com/github/chrBrd/QuickForms)

Quick Forms
=========

A Symfony bundle to allow the creation of forms from YML files.

Installation
---

The bundle can be installed using Composer:

    composer require binaryspanner/quickforms
    
Use
---

First the bundle needs registered in the Symfony app's kernel:

```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = [
        // ...
        new BinarySpanner\QuickForms\QuickFormsBundle(),
    ];
    
    // ...
}
```

When using the default settings the bundle will search for form files in `Resources/forms` in 
the kernel's root directory (normally `"path-to-Symfony-project"/app/`).

To load form files the filenames have to be added to the bundle's settings in `config.yml`:

```yaml
# app/config/config.yml
# ...
quick_forms:
    file_names: [ 'file1.yml', 'file2.yml', ...]
```

An example form file can be found in the bundle's `Resources/forms` directory. 
This form file will be loaded by default when the bundle's `file_names` property is unset in
`config.yml`.

The directories the bundle searches in can be changed by modifying the `root_paths` and/or
`directory_paths` bundle settings in `config.yml`:

```yaml
# app/config/config.yml
# ...
quick_forms:
    root_paths: [ '%kernel.root_dir%', '%kernel.root_dir%/../src' ]
    directory_paths: [ 'custom/forms' ]
    file_names: [ 'file1.yml', 'file2.yml', ...]
```

Note that absolute paths can be used in the `directory_paths` setting.

Once the form layout files have been setup a view template needs to be created - if you're using
Twig you can create Symfony forms as normal:

```twig
{# app/Resources/views/quickform/form.html.twig #}
{% extends 'base.html.twig' %}
 
{% block body %}
    Form One:
    {% form_theme forms.example_form_one.view forms.example_form_one.theme %}
    {{ form_start(forms.example_form_one.view) }}
    {{ form_widget(forms.example_form_one.view) }}
    {{ form_end(forms.example_form_one.view) }}
 
    Form Two:
    {% form_theme forms.example_form_two.view forms.example_form_two.theme %}
    {{ form_start(forms.example_form_two.view) }}
    {{ form_widget(forms.example_form_two.view) }}
    {{ form_end(forms.example_form_two.view) }}
{% endblock %}
```

Finally the views need to be loaded in the controller:

```php
// src/AppBundle/Controller/DefaultController.php
// ...
/**
 * @Route("/quickforms", name="quickforms")
 */
public function quickForms()
{
    $quickForms = $this->get('quick_forms');

    $forms = $quickForms->loadForms();

    return $this->render('quickform/form.html.twig', array(
        'forms' => $forms
    ));
}
```