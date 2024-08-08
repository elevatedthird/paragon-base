# Accordion
This is a simple accordion block that can be used to display content in a collapsible manner.

## Usage

```twig
// block usage hookup
// block--accordion.html.twig
  {% embed 'kinetic:accordion' with {
    heading: content.field_heading.0,
    accordion_id,
    description: content.field_description.0,
    behavior_name: 'kinetic-accordion',
    accodion_item_count: content.field_p_items['#items']|length,
  } %}
    {% block accordion_item_count %}
      {{ content.field_p_items[accordion_item_count] }}
    {% endblock %}
  {% endembed %}
  
// paragraph--accodion-item.html.twig
{% embed 'kinetic:accordion-item' with { heading: content.field_heading.0, parent_id, open: index == 0, key: index} %}
  {% block body %}
    {{ content.field_description.0 }}
  {% endblock %}
{% endembed %}


// usages outside of a block context

  {% set accordion_id = 'accordion--' ~ random() %}
  {% set parent_id = accordion_id %}
  {% embed 'kinetic:accordion' with {
    heading: 'This is the heading',
    accordion_id,
    description: 'This is the description',
    behavior_name: 'kinetic-accordion',
    accodion_item_count: items,
  } %}
    {% block accordion_item_count %}
        {% embed 'kinetic:accordion-item' with { heading: 'accordion heading', parent_id, open: accordion_item_count == 0, key: accordion_item_count} %}
          {% block body %}
            {{ items[accordion_item_count].body }}
          {% endblock %}
        {% endembed %}
    {% endblock %}
  {% endembed %}
```

## Additional information
https://getbootstrap.com/docs/5.0/components/accordion/
