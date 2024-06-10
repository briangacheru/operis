<?php
include "header.php";
include "sidebar.php";
include "nav.php";
?>
    <div class="card shadow-none border mb-3">
        <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(assets/img/illustrations/corner-6.png);">
        </div>
        <!--/.bg-holder-->

        <div class="card-header z-1">
            <div class="row flex-between-center gx-0">
                <div class="col-lg-auto d-flex align-items-center">
                    <h4 class="mb-0 text-primary fw-bold">All <span class="text-info fw-medium"> Tasks</span></h4>
                </div>
                <div class="col-lg-auto pt-3 pt-lg-0">
                    <form class="row flex-lg-column flex-xxl-row gx-3 gy-2 align-items-center align-items-lg-start align-items-xxl-center">
                        <div class="col-auto">
                        </div>
                        <div class="col-md-auto position-relative">
                            <h6 class="mb-1 text-primary"></h6>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<div class="row  g-3 mb-3">
    <div class="col">
        <div class="card mb-3">
            <div class="card-body p-0">
                <div class="tab-content">
                    <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-41cf422d-2a1d-40e2-b92a-ceac8cdfaca0" id="dom-41cf422d-2a1d-40e2-b92a-ceac8cdfaca0">
                        <div class="card shadow-none">
                            <div class="card-header">
                                <div class="row flex-between-center">
                                    <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                                        <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">Recent Purchases </h5>
                                    </div>
                                    <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                                        <div class="d-none" id="table-simple-pagination-actions">
                                            <div class="d-flex">
                                                <select class="form-select form-select-sm" aria-label="Bulk actions">
                                                    <option selected="">Bulk actions</option>
                                                    <option value="Refund">Refund</option>
                                                    <option value="Delete">Delete</option>
                                                    <option value="Archive">Archive</option>
                                                </select>
                                                <button class="btn btn-falcon-default btn-sm ms-2" type="button">Apply</button>
                                            </div>
                                        </div>
                                        <div id="table-simple-pagination-replace-element">
                                            <button class="btn btn-falcon-default btn-sm" type="button"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New</span></button>
                                            <button class="btn btn-falcon-default btn-sm mx-2" type="button"><span class="fas fa-filter" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Filter</span></button>
                                            <button class="btn btn-falcon-default btn-sm" type="button"><span class="fas fa-external-link-alt" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Export</span></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body px-0 pt-0">
                                <table class="table table-sm table-striped mb-0 overflow-hidden data-table fs-10" data-datatables='{"responsive":true,"pagingType":"simple","lengthChange":true,"searching":true,"pageLength":10,"language":{"info":"_START_ to _END_ Tasks of _TOTAL_"}}'>
                                    <thead class="bg-200">
                                    <tr>
                                        <th class="text-900 no-sort white-space-nowrap">
                                            <div class="form-check mb-0 d-flex align-items-center">
                                                <input class="form-check-input" id="checkbox-bulk-item-select" type="checkbox" data-bulk-select='{"body":"table-simple-pagination-body","actions":"table-simple-pagination-actions","replacedElement":"table-simple-pagination-replace-element"}' />
                                            </div>
                                        </th>
                                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Customer</th>
                                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Email</th>
                                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Product</th>
                                        <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center">Payment</th>
                                        <th class="text-900 sort pe-1 align-middle white-space-nowrap text-end">Amount</th>
                                        <th class="text-900 no-sort pe-1 align-middle data-table-row-action"></th>
                                    </tr>
                                    </thead>
                                    <tbody class="list" id="table-simple-pagination-body">
                                    <tr class="btn-reveal-trigger">
                                        <td class="align-middle" style="width: 28px;">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="simple-pagination-item-0" data-bulk-select-row="data-bulk-select-row" />
                                            </div>
                                        </td>
                                        <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="../../app/e-commerce/customer-details.html">Sylvia Plath</a></td>
                                        <td class="align-middle white-space-nowrap email">john@gmail.com</td>
                                        <td class="align-middle white-space-nowrap product">Slick - Drag &amp; Drop Bootstrap Generator</td>
                                        <td class="align-middle text-center fs-9 white-space-nowrap payment"><span class="badge badge rounded-pill badge-subtle-success">Success<span class="ms-1 fas fa-check" data-fa-transform="shrink-2"></span></span>
                                        </td>
                                        <td class="align-middle text-end amount">$99</td>
                                        <td class="align-middle white-space-nowrap text-end">
                                            <div class="dropstart font-sans-serif position-static d-inline-block">
                                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-simple-pagination-table-item-0" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-simple-pagination-table-item-0"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Edit</a><a class="dropdown-item" href="#!">Refund</a>
                                                    <div class="dropdown-divider"></div><a class="dropdown-item text-warning" href="#!">Archive</a><a class="dropdown-item text-danger" href="#!">Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="align-middle" style="width: 28px;">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="simple-pagination-item-1" data-bulk-select-row="data-bulk-select-row" />
                                            </div>
                                        </td>
                                        <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="../../app/e-commerce/customer-details.html">Homer</a></td>
                                        <td class="align-middle white-space-nowrap email">sylvia@mail.ru</td>
                                        <td class="align-middle white-space-nowrap product">Bose SoundSport Wireless Headphones</td>
                                        <td class="align-middle text-center fs-9 white-space-nowrap payment"><span class="badge badge rounded-pill badge-subtle-success">Success<span class="ms-1 fas fa-check" data-fa-transform="shrink-2"></span></span>
                                        </td>
                                        <td class="align-middle text-end amount">$634</td>
                                        <td class="align-middle white-space-nowrap text-end">
                                            <div class="dropstart font-sans-serif position-static d-inline-block">
                                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-simple-pagination-table-item-1" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-simple-pagination-table-item-1"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Edit</a><a class="dropdown-item" href="#!">Refund</a>
                                                    <div class="dropdown-divider"></div><a class="dropdown-item text-warning" href="#!">Archive</a><a class="dropdown-item text-danger" href="#!">Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="align-middle" style="width: 28px;">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="simple-pagination-item-2" data-bulk-select-row="data-bulk-select-row" />
                                            </div>
                                        </td>
                                        <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="../../app/e-commerce/customer-details.html">Edgar Allan Poe</a></td>
                                        <td class="align-middle white-space-nowrap email">edgar@yahoo.com</td>
                                        <td class="align-middle white-space-nowrap product">All-New Fire HD 8 Kids Edition Tablet</td>
                                        <td class="align-middle text-center fs-9 white-space-nowrap payment"><span class="badge badge rounded-pill badge-subtle-secondary">Blocked<span class="ms-1 fas fa-ban" data-fa-transform="shrink-2"></span></span>
                                        </td>
                                        <td class="align-middle text-end amount">$199</td>
                                        <td class="align-middle white-space-nowrap text-end">
                                            <div class="dropstart font-sans-serif position-static d-inline-block">
                                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-simple-pagination-table-item-2" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-simple-pagination-table-item-2"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Edit</a><a class="dropdown-item" href="#!">Refund</a>
                                                    <div class="dropdown-divider"></div><a class="dropdown-item text-warning" href="#!">Archive</a><a class="dropdown-item text-danger" href="#!">Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="align-middle" style="width: 28px;">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="simple-pagination-item-3" data-bulk-select-row="data-bulk-select-row" />
                                            </div>
                                        </td>
                                        <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="../../app/e-commerce/customer-details.html">William Butler Yeats</a></td>
                                        <td class="align-middle white-space-nowrap email">william@gmail.com</td>
                                        <td class="align-middle white-space-nowrap product">Apple iPhone XR (64GB)</td>
                                        <td class="align-middle text-center fs-9 white-space-nowrap payment"><span class="badge badge rounded-pill badge-subtle-success">Success<span class="ms-1 fas fa-check" data-fa-transform="shrink-2"></span></span>
                                        </td>
                                        <td class="align-middle text-end amount">$798</td>
                                        <td class="align-middle white-space-nowrap text-end">
                                            <div class="dropstart font-sans-serif position-static d-inline-block">
                                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-simple-pagination-table-item-3" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-simple-pagination-table-item-3"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Edit</a><a class="dropdown-item" href="#!">Refund</a>
                                                    <div class="dropdown-divider"></div><a class="dropdown-item text-warning" href="#!">Archive</a><a class="dropdown-item text-danger" href="#!">Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="align-middle" style="width: 28px;">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="simple-pagination-item-4" data-bulk-select-row="data-bulk-select-row" />
                                            </div>
                                        </td>
                                        <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="../../app/e-commerce/customer-details.html">Rabindranath Tagore</a></td>
                                        <td class="align-middle white-space-nowrap email">tagore@twitter.com</td>
                                        <td class="align-middle white-space-nowrap product">ASUS Chromebook C202SA-YS02 11.6&quot;</td>
                                        <td class="align-middle text-center fs-9 white-space-nowrap payment"><span class="badge badge rounded-pill badge-subtle-secondary">Blocked<span class="ms-1 fas fa-ban" data-fa-transform="shrink-2"></span></span>
                                        </td>
                                        <td class="align-middle text-end amount">$318</td>
                                        <td class="align-middle white-space-nowrap text-end">
                                            <div class="dropstart font-sans-serif position-static d-inline-block">
                                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-simple-pagination-table-item-4" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-simple-pagination-table-item-4"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Edit</a><a class="dropdown-item" href="#!">Refund</a>
                                                    <div class="dropdown-divider"></div><a class="dropdown-item text-warning" href="#!">Archive</a><a class="dropdown-item text-danger" href="#!">Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="align-middle" style="width: 28px;">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="simple-pagination-item-5" data-bulk-select-row="data-bulk-select-row" />
                                            </div>
                                        </td>
                                        <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="../../app/e-commerce/customer-details.html">Emily Dickinson</a></td>
                                        <td class="align-middle white-space-nowrap email">emily@gmail.com</td>
                                        <td class="align-middle white-space-nowrap product">Mirari OK to Wake! Alarm Clock &amp; Night-Light</td>
                                        <td class="align-middle text-center fs-9 white-space-nowrap payment"><span class="badge badge rounded-pill badge-subtle-warning">Pending<span class="ms-1 fas fa-stream" data-fa-transform="shrink-2"></span></span>
                                        </td>
                                        <td class="align-middle text-end amount">$11</td>
                                        <td class="align-middle white-space-nowrap text-end">
                                            <div class="dropstart font-sans-serif position-static d-inline-block">
                                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-simple-pagination-table-item-5" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-simple-pagination-table-item-5"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Edit</a><a class="dropdown-item" href="#!">Refund</a>
                                                    <div class="dropdown-divider"></div><a class="dropdown-item text-warning" href="#!">Archive</a><a class="dropdown-item text-danger" href="#!">Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="align-middle" style="width: 28px;">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="simple-pagination-item-6" data-bulk-select-row="data-bulk-select-row" />
                                            </div>
                                        </td>
                                        <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="../../app/e-commerce/customer-details.html">Giovanni Boccaccio</a></td>
                                        <td class="align-middle white-space-nowrap email">giovanni@outlook.com</td>
                                        <td class="align-middle white-space-nowrap product">Summer Infant Contoured Changing Pad</td>
                                        <td class="align-middle text-center fs-9 white-space-nowrap payment"><span class="badge badge rounded-pill badge-subtle-success">Success<span class="ms-1 fas fa-check" data-fa-transform="shrink-2"></span></span>
                                        </td>
                                        <td class="align-middle text-end amount">$31</td>
                                        <td class="align-middle white-space-nowrap text-end">
                                            <div class="dropstart font-sans-serif position-static d-inline-block">
                                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-simple-pagination-table-item-6" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-simple-pagination-table-item-6"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Edit</a><a class="dropdown-item" href="#!">Refund</a>
                                                    <div class="dropdown-divider"></div><a class="dropdown-item text-warning" href="#!">Archive</a><a class="dropdown-item text-danger" href="#!">Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="align-middle" style="width: 28px;">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="simple-pagination-item-7" data-bulk-select-row="data-bulk-select-row" />
                                            </div>
                                        </td>
                                        <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="../../app/e-commerce/customer-details.html">Oscar Wilde</a></td>
                                        <td class="align-middle white-space-nowrap email">oscar@hotmail.com</td>
                                        <td class="align-middle white-space-nowrap product">Munchkin 6 Piece Fork and Spoon Set</td>
                                        <td class="align-middle text-center fs-9 white-space-nowrap payment"><span class="badge badge rounded-pill badge-subtle-success">Success<span class="ms-1 fas fa-check" data-fa-transform="shrink-2"></span></span>
                                        </td>
                                        <td class="align-middle text-end amount">$43</td>
                                        <td class="align-middle white-space-nowrap text-end">
                                            <div class="dropstart font-sans-serif position-static d-inline-block">
                                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-simple-pagination-table-item-7" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-simple-pagination-table-item-7"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Edit</a><a class="dropdown-item" href="#!">Refund</a>
                                                    <div class="dropdown-divider"></div><a class="dropdown-item text-warning" href="#!">Archive</a><a class="dropdown-item text-danger" href="#!">Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="align-middle" style="width: 28px;">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="simple-pagination-item-8" data-bulk-select-row="data-bulk-select-row" />
                                            </div>
                                        </td>
                                        <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="../../app/e-commerce/customer-details.html">John Doe</a></td>
                                        <td class="align-middle white-space-nowrap email">doe@gmail.com</td>
                                        <td class="align-middle white-space-nowrap product">Falcon - Responsive Dashboard Template</td>
                                        <td class="align-middle text-center fs-9 white-space-nowrap payment"><span class="badge badge rounded-pill badge-subtle-success">Success<span class="ms-1 fas fa-check" data-fa-transform="shrink-2"></span></span>
                                        </td>
                                        <td class="align-middle text-end amount">$57</td>
                                        <td class="align-middle white-space-nowrap text-end">
                                            <div class="dropstart font-sans-serif position-static d-inline-block">
                                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-simple-pagination-table-item-8" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-simple-pagination-table-item-8"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Edit</a><a class="dropdown-item" href="#!">Refund</a>
                                                    <div class="dropdown-divider"></div><a class="dropdown-item text-warning" href="#!">Archive</a><a class="dropdown-item text-danger" href="#!">Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="align-middle" style="width: 28px;">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="simple-pagination-item-9" data-bulk-select-row="data-bulk-select-row" />
                                            </div>
                                        </td>
                                        <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="../../app/e-commerce/customer-details.html">Emma Watson</a></td>
                                        <td class="align-middle white-space-nowrap email">emma@gmail.com</td>
                                        <td class="align-middle white-space-nowrap product">Apple iPhone XR (64GB)</td>
                                        <td class="align-middle text-center fs-9 white-space-nowrap payment"><span class="badge badge rounded-pill badge-subtle-secondary">Blocked<span class="ms-1 fas fa-ban" data-fa-transform="shrink-2"></span></span>
                                        </td>
                                        <td class="align-middle text-end amount">$999</td>
                                        <td class="align-middle white-space-nowrap text-end">
                                            <div class="dropstart font-sans-serif position-static d-inline-block">
                                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-simple-pagination-table-item-9" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-simple-pagination-table-item-9"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Edit</a><a class="dropdown-item" href="#!">Refund</a>
                                                    <div class="dropdown-divider"></div><a class="dropdown-item text-warning" href="#!">Archive</a><a class="dropdown-item text-danger" href="#!">Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="align-middle" style="width: 28px;">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="simple-pagination-item-10" data-bulk-select-row="data-bulk-select-row" />
                                            </div>
                                        </td>
                                        <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="../../app/e-commerce/customer-details.html">Sylvia Plath</a></td>
                                        <td class="align-middle white-space-nowrap email">plath@yahoo.com</td>
                                        <td class="align-middle white-space-nowrap product">All-New Fire HD 8 Kids Edition Tablet</td>
                                        <td class="align-middle text-center fs-9 white-space-nowrap payment"><span class="badge badge rounded-pill badge-subtle-warning">Pending<span class="ms-1 fas fa-stream" data-fa-transform="shrink-2"></span></span>
                                        </td>
                                        <td class="align-middle text-end amount">$199</td>
                                        <td class="align-middle white-space-nowrap text-end">
                                            <div class="dropstart font-sans-serif position-static d-inline-block">
                                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-simple-pagination-table-item-10" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-simple-pagination-table-item-10"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Edit</a><a class="dropdown-item" href="#!">Refund</a>
                                                    <div class="dropdown-divider"></div><a class="dropdown-item text-warning" href="#!">Archive</a><a class="dropdown-item text-danger" href="#!">Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="align-middle" style="width: 28px;">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="simple-pagination-item-11" data-bulk-select-row="data-bulk-select-row" />
                                            </div>
                                        </td>
                                        <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="../../app/e-commerce/customer-details.html">Rabindranath Tagore</a></td>
                                        <td class="align-middle white-space-nowrap email">Rabindra@gmail.com</td>
                                        <td class="align-middle white-space-nowrap product">Apple iPhone XR (64GB)</td>
                                        <td class="align-middle text-center fs-9 white-space-nowrap payment"><span class="badge badge rounded-pill badge-subtle-secondary">Blocked<span class="ms-1 fas fa-ban" data-fa-transform="shrink-2"></span></span>
                                        </td>
                                        <td class="align-middle text-end amount">$999</td>
                                        <td class="align-middle white-space-nowrap text-end">
                                            <div class="dropstart font-sans-serif position-static d-inline-block">
                                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-simple-pagination-table-item-11" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-simple-pagination-table-item-11"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Edit</a><a class="dropdown-item" href="#!">Refund</a>
                                                    <div class="dropdown-divider"></div><a class="dropdown-item text-warning" href="#!">Archive</a><a class="dropdown-item text-danger" href="#!">Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="align-middle" style="width: 28px;">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="simple-pagination-item-12" data-bulk-select-row="data-bulk-select-row" />
                                            </div>
                                        </td>
                                        <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="../../app/e-commerce/customer-details.html">Anila Wilde</a></td>
                                        <td class="align-middle white-space-nowrap email">anila@yahoo.com</td>
                                        <td class="align-middle white-space-nowrap product">All-New Fire HD 8 Kids Edition Tablet</td>
                                        <td class="align-middle text-center fs-9 white-space-nowrap payment"><span class="badge badge rounded-pill badge-subtle-warning">Pending<span class="ms-1 fas fa-stream" data-fa-transform="shrink-2"></span></span>
                                        </td>
                                        <td class="align-middle text-end amount">$199</td>
                                        <td class="align-middle white-space-nowrap text-end">
                                            <div class="dropstart font-sans-serif position-static d-inline-block">
                                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-simple-pagination-table-item-12" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-simple-pagination-table-item-12"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Edit</a><a class="dropdown-item" href="#!">Refund</a>
                                                    <div class="dropdown-divider"></div><a class="dropdown-item text-warning" href="#!">Archive</a><a class="dropdown-item text-danger" href="#!">Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="align-middle" style="width: 28px;">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="simple-pagination-item-13" data-bulk-select-row="data-bulk-select-row" />
                                            </div>
                                        </td>
                                        <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="../../app/e-commerce/customer-details.html">Jack Watson </a></td>
                                        <td class="align-middle white-space-nowrap email">Jack@gmail.com</td>
                                        <td class="align-middle white-space-nowrap product">Apple iPhone XR (64GB)</td>
                                        <td class="align-middle text-center fs-9 white-space-nowrap payment"><span class="badge badge rounded-pill badge-subtle-secondary">Blocked<span class="ms-1 fas fa-ban" data-fa-transform="shrink-2"></span></span>
                                        </td>
                                        <td class="align-middle text-end amount">$999</td>
                                        <td class="align-middle white-space-nowrap text-end">
                                            <div class="dropstart font-sans-serif position-static d-inline-block">
                                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-simple-pagination-table-item-13" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-simple-pagination-table-item-13"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Edit</a><a class="dropdown-item" href="#!">Refund</a>
                                                    <div class="dropdown-divider"></div><a class="dropdown-item text-warning" href="#!">Archive</a><a class="dropdown-item text-danger" href="#!">Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
include "footer.php";
?>