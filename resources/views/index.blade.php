<x-esign::layout :title="$title" :signerId="$signer->id">
    @pushonce('css')
        <style></style>
    @endpushonce

    <div>
        <div class="xl:pl-72">
            <div
                class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-6 border-b border-white/5 bg-gray-900 px-4 shadow-sm sm:px-6 lg:px-8"
            >
                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <form class="flex flex-1" action="#" method="GET">
                        <label for="search-field" class="sr-only">Search</label>
                        <div class="relative w-full">
                            <svg
                                class="pointer-events-none absolute inset-y-0 left-0 h-full w-5 text-gray-500"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <input
                                id="search-field"
                                class="block h-full w-full border-0 bg-transparent py-0 pl-8 pr-0 text-white focus:ring-0 sm:text-sm"
                                placeholder="Search..."
                                type="search"
                                name="search"
                            />
                        </div>
                    </form>
                </div>
            </div>

            <form id="signersForm">
                <main class="lg:pr-96">
                    <header
                        class="flex items-center justify-between border-b border-white/5 px-4 py-4 sm:px-6 sm:py-6 lg:px-8"
                    >
                        <label class="text-white">
                            <input type="file" id="document" name="document" />
                        </label>
                    </header>

                    <div class="p-4" id="documentContainer"></div>
                </main>

                <aside
                    class="bg-black/10 lg:fixed lg:bottom-0 lg:right-0 lg:top-16 lg:w-96 lg:overflow-y-auto lg:border-l lg:border-white/5"
                >
                    <header
                        class="flex items-center justify-between border-b border-white/5 px-4 py-4 sm:px-6 sm:py-6 lg:px-8"
                    >
                        <label><select id="signers"></select></label>
                        <div class="flex flex-row gap-4">
                            <button
                                type="button"
                                id="addSigner"
                                class="bg-white border border-white px-4 rounded"
                            >
                                Add Signer
                            </button>
                            <button
                                type="button"
                                id="saveButton"
                                class="bg-white border border-white px-4 rounded"
                            >
                                Save
                            </button>
                        </div>
                    </header>
                    <div class="elementsContainer">
                        <button
                            type="button"
                            class="inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            <svg
                                class="-ml-0.5 h-5 w-5"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            Element1
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            <svg
                                class="-ml-0.5 h-5 w-5"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            Element2
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            <svg
                                class="-ml-0.5 h-5 w-5"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            Elemen3
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            <svg
                                class="-ml-0.5 h-5 w-5"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            Element4
                        </button>
                    </div>
                </aside>
            </form>
        </div>
    </div>

    @pushonce('js')
        <script type="module"></script>
    @endpushonce
</x-esign::layout>
