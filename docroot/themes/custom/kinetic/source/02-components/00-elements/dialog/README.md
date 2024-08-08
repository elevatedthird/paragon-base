# Dialog

A bootstrap modal dialog.

## Usage
Ensure you combine the trigger and dialog components to create a dialog.
Ensure you set the toggle_id to modal on the trigger.
```twig
    {% set toggle_id = "modal" %}
    {% set target_id = toggle_id ~ "--" ~ random() %}
    {{ include('kinetic:trigger', { target_id, toggle_id, text: "Open Sesame" }, with_context = false ) }}
    {% embed "kinetic:dialog" with {
      target_id: target_id,
      dialog_title: 'Dialog Title',
    } only %}
      {% block _dialog_body %}
        <img src="/themes/custom/kinetic/source/01-base/assets/placeholder.jpeg" alt="">
        <p>Sint rerum occaecat nisl hic aliquet voluptatem, cillum voluptatem eros neque, consequatur aut egestas adipisci. Euismod, deserunt libero torquent facilis.</p>
      {% endblock %}
    {% endembed %}
```
## Additional information
https://getbootstrap.com/docs/5.0/components/modal/
