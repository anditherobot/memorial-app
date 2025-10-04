# Design System

This document outlines the UI components that make up the Memorial application's design system.

## Components

### Alert

- **Path:** `resources/views/components/ui/alert.blade.php`
- **Description:** Displays a prominent message to the user.
- **Props:**
    - `variant`: `neutral` (default), `info`, `success`, `warning`, `danger`
    - `style`: `soft` (default), `solid`
- **Usage:**
    ```blade
    <x-ui.alert variant="success" style="solid">
        This is a success message.
    </x-ui.alert>
    ```

### Badge

- **Path:** `resources/views/components/ui/badge.blade.php`
- **Description:** A small component to highlight information.
- **Props:**
    - `variant`: `neutral` (default), `info`, `success`, `warning`, `danger`, `dark`
- **Usage:**
    ```blade
    <x-ui.badge variant="info">New</x-ui.badge>
    ```

### Breadcrumb

- **Path:** `resources/views/components/ui/breadcrumb.blade.php`
- **Description:** A navigational aid that shows the user's location in the app.
- **Usage:**
    ```blade
    <x-ui.breadcrumb>
        <li>
            <span class="text-gray-500 mx-2">/</span>
        </li>
        <li>
            <a href="#" class="text-gray-500 hover:text-gray-700">Admin</a>
        </li>
    </x-ui.breadcrumb>
    ```

### Button

- **Path:** `resources/views/components/ui/button.blade.php`
- **Description:** A standard button element.
- **Props:**
    - `variant`: `primary` (default), `secondary`, `ghost`, `danger`, `info`, `outline`, `brand-outline`
    - `size`: `sm`, `md` (default), `lg`
    - `type`: `button` (default), `submit`, `reset`
- **Usage:**
    ```blade
    <x-ui.button variant="primary" size="lg">Click me</x-ui.button>
    ```

### Button Link

- **Path:** `resources/views/components/ui/button-link.blade.php`
- **Description:** A link styled as a button.
- **Props:**
    - `href`: The URL to link to.
    - `variant`: `primary` (default), `secondary`, `ghost`, `danger`, `info`, `outline`, `brand-outline`
    - `size`: `sm`, `md` (default), `lg`
- **Usage:**
    ```blade
    <x-ui.button-link href="/home" variant="secondary">Go Home</x-ui.button-link>
    ```

### Card

- **Path:** `resources/views/components/ui/card.blade.php`
- **Description:** A container for content.
- **Props:**
    - `hover`: `false` (default), `true`
    - `padding`: `p-4` (default)
- **Usage:**
    ```blade
    <x-ui.card padding="p-6">
        This is a card.
    </x-ui.card>
    ```

### Input

- **Path:** `resources/views/components/ui/input.blade.php`
- **Description:** A standard text input field.
- **Props:**
    - `name`: The name of the input.
    - `type`: `text` (default), `email`, `password`, etc.
    - `value`: The initial value of the input.
    - `error`: The name of the error bag to check for validation errors.
- **Usage:**
    ```blade
    <x-ui.input name="email" type="email" placeholder="Enter your email" />
    ```

### Label

- **Path:** `resources/views/components/ui/label.blade.php`
- **Description:** A label for a form field.
- **Props:**
    - `for`: The ID of the form field this label is for.
- **Usage:**
    ```blade
    <x-ui.label for="email">Email Address</x-ui.label>
    ```

### Nav Link

- **Path:** `resources/views/components/ui/nav-link.blade.php`
- **Description:** A link for use in navigation menus.
- **Props:**
    - `href`: The URL to link to.
    - `active`: `false` (default), `true`
- **Usage:**
    ```blade
    <x-ui.nav-link href="/home" :active="request()->routeIs('home')">Home</x-ui.nav-link>
    ```

### Section

- **Path:** `resources/views/components/ui/section.blade.php`
- **Description:** A semantic section of a page.
- **Props:**
    - `title`: The title of the section.
    - `description`: A description of the section.
- **Usage:**
    ```blade
    <x-ui.section title="My Section" description="This is a section of the page.">
        ...
    </x-ui.section>
    ```

### Site Footer

- **Path:** `resources/views/components/ui/site-footer.blade.php`
- **Description:** The site-wide footer.
- **Usage:**
    ```blade
    <x-ui.site-footer />
    ```

### Site Header

- **Path:** `resources/views/components/ui/site-header.blade.php`
- **Description:** The site-wide header.
- **Usage:**
    ```blade
    <x-ui.site-header />
    ```

### Textarea

- **Path:** `resources/views/components/ui/textarea.blade.php`
- **Description:** A standard textarea field.
- **Props:**
    - `name`: The name of the textarea.
    - `rows`: `4` (default)
    - `error`: The name of the error bag to check for validation errors.
- **Usage:**
    ```blade
    <x-ui.textarea name="message" rows="6" placeholder="Enter your message"></x-ui.textarea>
    ```