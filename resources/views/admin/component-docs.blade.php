@extends('layouts.app')

@section('title', 'Component Kitchen Sink')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Component Kitchen Sink</h1>
            <p class="text-gray-600">Interactive examples of all UI components in the design system</p>
        </div>

        {{-- Alert --}}
        <x-ui.section title="Alert" description="Displays a prominent message to the user">
            <div class="space-y-3">
                <x-ui.alert variant="neutral" style="soft">This is a neutral soft alert</x-ui.alert>
                <x-ui.alert variant="info" style="soft">This is an info soft alert</x-ui.alert>
                <x-ui.alert variant="success" style="soft">This is a success soft alert</x-ui.alert>
                <x-ui.alert variant="warning" style="soft">This is a warning soft alert</x-ui.alert>
                <x-ui.alert variant="danger" style="soft">This is a danger soft alert</x-ui.alert>

                <div class="pt-2"></div>

                <x-ui.alert variant="neutral" style="solid">This is a neutral solid alert</x-ui.alert>
                <x-ui.alert variant="info" style="solid">This is an info solid alert</x-ui.alert>
                <x-ui.alert variant="success" style="solid">This is a success solid alert</x-ui.alert>
                <x-ui.alert variant="warning" style="solid">This is a warning solid alert</x-ui.alert>
                <x-ui.alert variant="danger" style="solid">This is a danger solid alert</x-ui.alert>
            </div>
        </x-ui.section>

        {{-- Badge --}}
        <x-ui.section title="Badge" description="A small component to highlight information">
            <div class="flex flex-wrap gap-2">
                <x-ui.badge variant="neutral">Neutral</x-ui.badge>
                <x-ui.badge variant="info">Info</x-ui.badge>
                <x-ui.badge variant="success">Success</x-ui.badge>
                <x-ui.badge variant="warning">Warning</x-ui.badge>
                <x-ui.badge variant="danger">Danger</x-ui.badge>
                <x-ui.badge variant="dark">Dark</x-ui.badge>
            </div>
        </x-ui.section>

        {{-- Breadcrumb --}}
        <x-ui.section title="Breadcrumb" description="A navigational aid that shows the user's location">
            <x-ui.breadcrumb>
                <li>
                    <a href="#" class="text-gray-500 hover:text-gray-700">Home</a>
                </li>
                <li>
                    <span class="text-gray-500 mx-2">/</span>
                </li>
                <li>
                    <a href="#" class="text-gray-500 hover:text-gray-700">Admin</a>
                </li>
                <li>
                    <span class="text-gray-500 mx-2">/</span>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">Components</span>
                </li>
            </x-ui.breadcrumb>
        </x-ui.section>

        {{-- Buttons --}}
        <x-ui.section title="Button" description="Standard button elements with various styles and sizes">
            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Variants</h4>
                    <div class="flex flex-wrap gap-2">
                        <x-ui.button variant="primary">Primary</x-ui.button>
                        <x-ui.button variant="secondary">Secondary</x-ui.button>
                        <x-ui.button variant="ghost">Ghost</x-ui.button>
                        <x-ui.button variant="danger">Danger</x-ui.button>
                        <x-ui.button variant="info">Info</x-ui.button>
                        <x-ui.button variant="outline">Outline</x-ui.button>
                        <x-ui.button variant="brand-outline">Brand Outline</x-ui.button>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Sizes</h4>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.button size="sm">Small</x-ui.button>
                        <x-ui.button size="md">Medium</x-ui.button>
                        <x-ui.button size="lg">Large</x-ui.button>
                    </div>
                </div>
            </div>
        </x-ui.section>

        {{-- Button Link --}}
        <x-ui.section title="Button Link" description="Links styled as buttons">
            <div class="flex flex-wrap gap-2">
                <x-ui.button-link href="#" variant="primary">Primary Link</x-ui.button-link>
                <x-ui.button-link href="#" variant="secondary">Secondary Link</x-ui.button-link>
                <x-ui.button-link href="#" variant="outline">Outline Link</x-ui.button-link>
            </div>
        </x-ui.section>

        {{-- Card --}}
        <x-ui.section title="Card" description="A container for content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-ui.card>
                    <h3 class="font-semibold mb-2">Default Card</h3>
                    <p class="text-gray-600">This is a card with default padding (p-4)</p>
                </x-ui.card>

                <x-ui.card padding="p-6">
                    <h3 class="font-semibold mb-2">Card with Custom Padding</h3>
                    <p class="text-gray-600">This card has p-6 padding</p>
                </x-ui.card>

                <x-ui.card :hover="true">
                    <h3 class="font-semibold mb-2">Hoverable Card</h3>
                    <p class="text-gray-600">This card has a hover effect</p>
                </x-ui.card>
            </div>
        </x-ui.section>

        {{-- Form Elements --}}
        <x-ui.section title="Form Elements" description="Inputs, labels, and textareas">
            <div class="max-w-md space-y-4">
                <div>
                    <x-ui.label for="example-text">Text Input</x-ui.label>
                    <x-ui.input name="example-text" type="text" placeholder="Enter some text" />
                </div>

                <div>
                    <x-ui.label for="example-email">Email Input</x-ui.label>
                    <x-ui.input name="example-email" type="email" placeholder="your@email.com" />
                </div>

                <div>
                    <x-ui.label for="example-password">Password Input</x-ui.label>
                    <x-ui.input name="example-password" type="password" placeholder="••••••••" />
                </div>

                <div>
                    <x-ui.label for="example-textarea">Textarea</x-ui.label>
                    <x-ui.textarea name="example-textarea" rows="4" placeholder="Enter a longer message..."></x-ui.textarea>
                </div>
            </div>
        </x-ui.section>

        {{-- Nav Link --}}
        <x-ui.section title="Nav Link" description="Navigation menu links">
            <div class="flex gap-4">
                <x-ui.nav-link href="#" :active="false">Inactive Link</x-ui.nav-link>
                <x-ui.nav-link href="#" :active="true">Active Link</x-ui.nav-link>
            </div>
        </x-ui.section>

        {{-- Documentation Link --}}
        <div class="mt-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <p class="text-sm text-gray-600">
                For detailed documentation including component paths and all available props, see
                <a href="{{ asset('docs/DESIGN_SYSTEM.md') }}" class="text-blue-600 hover:underline">DESIGN_SYSTEM.md</a>
            </p>
        </div>
    </div>
@endsection
