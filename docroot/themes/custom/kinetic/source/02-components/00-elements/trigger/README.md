# Trigger

In Bootstrap 5, the `data-bs-target` attribute is used to specify the target element that an interactive component should control or affect. This attribute is commonly seen in components like modals, collapsible elements, dropdowns, and carousels. It serves as a link between the trigger element (such as a button or a link) and the component that appears or changes in response.

Here’s how data-bs-target is used in different contexts:

Modals: For opening a modal window, data-bs-target is set on a button or link to indicate which modal it triggers. The value of the attribute is the ID selector of the modal element.
````
<button type="button" data-bs-toggle="modal" data-bs-target="#myModal">
  Launch modal
</button>
````
Collapsible (Accordion): In an accordion setup, data-bs-target specifies which panel should be opened or closed when the element is clicked.
````
<a href="#" data-bs-toggle="collapse" data-bs-target="#collapseExample">
  Link with href
</a>
````
Dropdowns: It links a button to a dropdown menu, indicating what should be displayed when the button is activated.
html
````
<button data-bs-toggle="dropdown" data-bs-target="#dropdownMenu">
  Dropdown button
</button>
````

This attribute is essential for ensuring that interactive components function correctly by targeting the correct elements within the DOM structure, enabling seamless interactions within the Bootstrap framework.
