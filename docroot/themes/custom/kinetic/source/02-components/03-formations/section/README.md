# Section

This component helps you generate layouts whether or not the content comes from Layout Builder.
Place styles that affect all layouts here.
## Usage

```html
{% embed 'kinetic:section' with {
  layout_id: 'banner',
  regions: ['first'],
  container_attributes: {
    first: {
      class: ['container'],
      id: 'my-id'
    }
  },
} %}
  {% block first %}
    My content for region 'first'
  {% endblock %}
{% endembed %}

OUTPUT:
------------

<div class='layout layout-banner' data-component-id='kinetic:section'>
  <div id='my-id' class='container layout__region layout__region-first'>
    My content for region 'first'
  </div>
</div>
```

## Additional information

Layout Builder template overrride:

- See templates/layout/hero-layout.html.twig
- See templates/layout/onecol-layout.html.twig
- See templates/layout/twocol-layout.html.twig

Non Layout Builder usage:
- See 02-blcoks/text-and-media