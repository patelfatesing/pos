<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>LiquorHub POS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta charset="utf-8" />
    <meta property="twitter:card" content="summary_large_image" />

    <style data-tag="reset-style-sheet">
        html {
            line-height: 1.15;
        }

        body {
            margin: 0;
        }

        * {
            box-sizing: border-box;
            border-width: 0;
            border-style: solid;
            -webkit-font-smoothing: antialiased;
        }

        p,
        li,
        ul,
        pre,
        div,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        figure,
        blockquote,
        figcaption {
            margin: 0;
            padding: 0;
        }

        button {
            background-color: transparent;
        }

        button,
        input,
        optgroup,
        select,
        textarea {
            font-family: inherit;
            font-size: 100%;
            line-height: 1.15;
            margin: 0;
        }

        button,
        select {

            text-transform: none;}button,[type="button"],
            [type="reset"],
            [type="submit"] {
                -webkit-appearance: button;

                color: inherit;}button::-moz-focus-inner,[type="button"]::-moz-focus-inner,
                [type="reset"]::-moz-focus-inner,
                [type="submit"]::-moz-focus-inner {
                    border-style: none;

                    padding: 0;}button:-moz-focus,[type="button"]:-moz-focus,
                    [type="reset"]:-moz-focus,
                    [type="submit"]:-moz-focus {
                        outline: 1px dotted ButtonText;
                    }

                    a {
                        color: inherit;
                        text-decoration: inherit;
                    }

                    input {
                        padding: 2px 4px;
                    }

                    img {
                        display: block;
                    }

                    details {
                        display: block;
                        margin: 0;
                        padding: 0;
                    }

                    summary::-webkit-details-marker {
                        display: none;}[data-thq="accordion"] [data-thq="accordion-content"] {
                            max-height: 0;
                            overflow: hidden;
                            transition: max-height 0.3s ease-in-out;

                            padding: 0;}[data-thq="accordion"] details[data-thq="accordion-trigger"][open]+[data-thq="accordion-content"] {
                                max-height: 1000vh;}details[data-thq="accordion-trigger"][open] summary [data-thq="accordion-icon"] {
                                    transform: rotate(180deg);
                                }

                                html {
                                    scroll-behavior: smooth
                                }
    </style>
    <style data-tag="default-style-sheet">
        html {
            font-family: Inter;
            font-size: 16px;
        }

        body {
            font-weight: 400;
            font-style: normal;
            text-decoration: none;
            text-transform: none;
            letter-spacing: normal;
            line-height: 1.15;
            color: var(--dl-color-theme-neutral-dark);
            background: var(--dl-color-theme-neutral-light);

            fill: var(--dl-color-theme-neutral-dark);
        }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/animate.css@4.1.1/animate.css" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=STIX+Two+Text:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet" href="https://unpkg.com/@teleporthq/teleport-custom-scripts/dist/style.css" />
</head>
<!-- Favicon -->
<link rel="shortcut icon" href="../assets/images/favicon.ico" />
<link rel="stylesheet" href="../assets/css/style_new_theme.css">
<link rel="stylesheet" href="../assets/css/index.css">

@livewireStyles
</head>

<body class=" color-light ">
    <!-- loader Start -->
    <div id="loading">
        <div id="loading-center">
        </div>
    </div>

    <div class="page-wrapper">

        <div class="main-screen-container1">
            <div class="main-screen-main-screen">
                <img src="{{ asset('assets//external/rectangle1004385-xk0s-1000w.png')}}" alt="Rectangle1004385"
                    class="main-screen-rectangle100" />
                <img src="{{ asset('assets//external/rectangle994386-89xn-200h.png')}}" alt="Rectangle994386"
                    class="main-screen-rectangle99" />
                <img src="{{ asset('assets//external/rectangle1014385-b70i-200h.png')}}" alt="Rectangle1014385"
                    class="main-screen-rectangle1011" />
                <div class="main-screen-sidebar">
                    <img src="{{ asset('assets//external/rectangle274411-m5c6-200w.png')}}" alt="Rectangle274411"
                        class="main-screen-rectangle27" />
                    <img src="{{ asset('assets//external/rectangle4594471-2wvr-200w.png')}}" alt="Rectangle4594471"
                        class="main-screen-rectangle459" />
                    <div class="main-screen-layer11"></div>
                </div>
                <img src="{{ asset('assets//external/image73a1ed2f33b74c9599cb101a7e1b7e5f14386-0of-200h.png')}}"
                    alt="IMAGE73a1ed2f33b74c9599cb101a7e1b7e5f14386"
                    class="main-screen-image73a1ed2f33b74c9599cb101a7e1b7e5f1" />
                <div class="main-screen-frame246">
                    <div class="main-screen-refresh1">
                        <div class="main-screen-group1">
                            <img src="{{ asset('assets//external/vector4386-ktti.svg')}}" alt="Vector4386"
                                class="main-screen-vector10" />
                            <img src="{{ asset('assets//external/vector4386-a3yh.svg')}}" alt="Vector4386"
                                class="main-screen-vector11" />
                        </div>
                    </div>
                    <img src="{{ asset('assets//external/expand14386-8mm8.svg')}}" alt="expand14386" class="main-screen-expand1" />
                    <img src="{{ asset('assets//external/bell14386-m6nd.svg')}}" alt="bell14386" class="main-screen-bell1" />
                    <div class="main-screen-frame245">
                        <div class="main-screen-group188">
                            <span class="main-screen-text10">English</span>
                            <div class="main-screen-layer12">
                                <div class="main-screen-group2">
                                    <img src="{{ asset('assets//external/vector4386-9x8.svg')}}" alt="Vector4386"
                                        class="main-screen-vector12" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <img src="{{ asset('assets//external/fi106093284386-op1f.svg')}}" alt="fi106093284386"
                        class="main-screen-fi10609328" />
                </div>
                <span class="main-screen-text11">Welcome ! Girish Panchal.</span>
                <div class="main-screen-group189">
                    <div class="main-screen-searchbar1">
                        <div class="main-screen-statelayer1">
                            <div class="main-screen-content1">
                                <span class="main-screen-text12 M3bodylarge">Barcode</span>
                            </div>
                        </div>
                    </div>
                    <img src="{{ asset('assets//external/barcoderead14386-czl.svg')}}" alt="barcoderead14386"
                        class="main-screen-barcoderead1" />
                </div>
                <div class="main-screen-searchbar2">
                    <div class="main-screen-statelayer2">
                        <div class="main-screen-content2">
                            <span class="main-screen-text13 M3bodylarge">Select Party</span>
                            <img src="{{ asset('assets//external/vector4386-v3td.svg')}}" alt="Vector4386"
                                class="main-screen-vector13" />
                        </div>
                    </div>
                </div>
                <div class="main-screen-group190">
                    <div class="main-screen-searchbar3">
                        <div class="main-screen-statelayer3">
                            <div class="main-screen-frame247">
                                <span class="main-screen-text14 M3bodylarge">Capture</span>
                                <img src="{{ asset('assets//external/camera114386-758.svg')}}" alt="camera114386"
                                    class="main-screen-camera11" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="main-screen-searchbar4">
                    <div class="main-screen-statelayer4">
                        <div class="main-screen-frame248">
                            <span class="main-screen-text15 M3bodylarge">
                                Scan Invoice No.
                            </span>
                            <img src="{{ asset('assets//external/qrscan14386-3kr.svg')}}" alt="qrscan14386"
                                class="main-screen-qrscan1" />
                        </div>
                    </div>
                </div>
                <div class="main-screen-searchbar5">
                    <div class="main-screen-statelayer5">
                        <div class="main-screen-content3">
                            <span class="main-screen-text16 M3bodylarge">search text</span>
                        </div>
                        <div class="main-screen-trailing-elements">
                            <div class="main-screen-search1">
                                <div class="main-screen-group3">
                                    <img src="{{ asset('assets//external/vector4386-ntm.svg')}}" alt="Vector4386"
                                        class="main-screen-vector14" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="main-screen-group193">
                    <img src="{{ asset('assets//external/rectangle1014386-z8hq-700w.png')}}" alt="Rectangle1014386"
                        class="main-screen-rectangle1012" />
                    <div class="main-screen-group192"></div>
                    <span class="main-screen-text17">Product</span>
                    <span class="main-screen-text18">Qty</span>
                    <span class="main-screen-text19">Price</span>
                    <span class="main-screen-text20">Total</span>
                    <span class="main-screen-text21">Action</span>
                    <div class="main-screen-group301">
                        <img src="{{ asset('assets//external/line14387-lkzt.svg')}}" alt="Line14387" class="main-screen-line11" />
                        <span class="main-screen-text22">
                            Royal Challenge Premium Deluxe Whisky 180ml
                        </span>
                        <div class="main-screen-group2391">
                            <div class="main-screen-group2371">
                                <div class="main-screen-group2361"></div>
                            </div>
                        </div>
                        <span class="main-screen-text23">₹ 210.00</span>
                        <span class="main-screen-text24">₹ 210.00</span>
                        <img src="{{ asset('assets//external/delete24dp1f1f1ffill0wght400grad0opsz2414387-u7ll.svg')}}"
                            alt="delete24dp1F1F1FFILL0wght400GRAD0opsz2414387"
                            class="main-screen-delete24dp1f1f1ffill0wght400grad0opsz2411" />
                    </div>
                    <div class="main-screen-group302">
                        <img src="{{ asset('assets//external/line14389-0cy.svg')}}" alt="Line14389" class="main-screen-line12" />
                        <span class="main-screen-text25">
                            Royal Challenge Premium Deluxe Whisky 180ml
                        </span>
                        <div class="main-screen-group2392">
                            <div class="main-screen-group2372">
                                <div class="main-screen-group2362"></div>
                            </div>
                        </div>
                        <span class="main-screen-text26">₹ 210.00</span>
                        <span class="main-screen-text27">₹ 210.00</span>
                        <img src="{{ asset('assets//external/delete24dp1f1f1ffill0wght400grad0opsz2414389-g7fe.svg')}}"
                            alt="delete24dp1F1F1FFILL0wght400GRAD0opsz2414389"
                            class="main-screen-delete24dp1f1f1ffill0wght400grad0opsz2412" />
                    </div>
                    <div class="main-screen-group303">
                        <img src="{{ asset('assets//external/line14389-wpg7.svg')}}" alt="Line14389" class="main-screen-line13" />
                        <span class="main-screen-text28">
                            Royal Challenge Premium Deluxe Whisky 180ml
                        </span>
                        <div class="main-screen-group2393">
                            <div class="main-screen-group2373">
                                <div class="main-screen-group2363"></div>
                            </div>
                        </div>
                        <span class="main-screen-text29">₹ 210.00</span>
                        <span class="main-screen-text30">₹ 210.00</span>
                        <img src="{{ asset('assets//external/delete24dp1f1f1ffill0wght400grad0opsz2414389-1pso.svg')}}"
                            alt="delete24dp1F1F1FFILL0wght400GRAD0opsz2414389"
                            class="main-screen-delete24dp1f1f1ffill0wght400grad0opsz2413" />
                    </div>
                    <div class="main-screen-group304">
                        <img src="{{ asset('assets//external/line14389-8tmn.svg')}}" alt="Line14389" class="main-screen-line14" />
                        <span class="main-screen-text31">
                            Royal Challenge Premium Deluxe Whisky 180ml
                        </span>
                        <div class="main-screen-group2394">
                            <div class="main-screen-group2374">
                                <div class="main-screen-group2364"></div>
                            </div>
                        </div>
                        <span class="main-screen-text32">₹ 210.00</span>
                        <span class="main-screen-text33">₹ 210.00</span>
                        <img src="{{ asset('assets//external/delete24dp1f1f1ffill0wght400grad0opsz2414389-d0fo.svg')}}"
                            alt="delete24dp1F1F1FFILL0wght400GRAD0opsz2414389"
                            class="main-screen-delete24dp1f1f1ffill0wght400grad0opsz2414" />
                    </div>
                    <div class="main-screen-group305">
                        <img src="{{ asset('assets//external/line14389-3yxo.svg')}}" alt="Line14389" class="main-screen-line15" />
                        <span class="main-screen-text34">
                            Royal Challenge Premium Deluxe Whisky 180ml
                        </span>
                        <div class="main-screen-group2395">
                            <div class="main-screen-group2375">
                                <div class="main-screen-group2365"></div>
                            </div>
                        </div>
                        <span class="main-screen-text35">₹ 210.00</span>
                        <span class="main-screen-text36">₹ 210.00</span>
                        <img src="{{ asset('assets//external/delete24dp1f1f1ffill0wght400grad0opsz2414389-sfz.svg')}}"
                            alt="delete24dp1F1F1FFILL0wght400GRAD0opsz2414389"
                            class="main-screen-delete24dp1f1f1ffill0wght400grad0opsz2415" />
                    </div>
                        
                </div>
                <div class="main-screen-frame266">
                    <div class="main-screen-frame262">
                        <div class="main-screen-frame-key10"></div>
                        <div class="main-screen-frame-key11"></div>
                        <div class="main-screen-frame-key12"></div>
                        <div class="main-screen-frame-key13">
                            <span class="main-screen-text40">+10</span>
                        </div>
                    </div>
                    <div class="main-screen-frame263">
                        <div class="main-screen-frame-key14"></div>
                        <div class="main-screen-frame-key15"></div>
                        <div class="main-screen-frame-key16"></div>
                        <div class="main-screen-frame-key17">
                            <span class="main-screen-text41">+20</span>
                        </div>
                    </div>
                    <div class="main-screen-frame264">
                        <div class="main-screen-frame-key18"></div>
                        <div class="main-screen-frame-key19"></div>
                        <div class="main-screen-frame-key20"></div>
                        <div class="main-screen-frame-key21">
                            <span class="main-screen-text42">+50</span>
                        </div>
                    </div>
                    <div class="main-screen-frame265">
                        <div class="main-screen-frame-key22"></div>
                        <div class="main-screen-frame-key23"></div>
                        <div class="main-screen-frame-key24">
                            <div class="main-screen-icons">
                                <div class="main-screen-group203">
                                    <img src="{{ asset('assets//external/vector4388-7mp9.svg')}}" alt="Vector4388"
                                        class="main-screen-vector15" />
                                </div>
                            </div>
                        </div>
                        <div class="main-screen-frame-key25">
                            <img src="{{ asset('assets//external/right4388-motb.svg')}}" alt="right4388"
                                class="main-screen-right1" />
                        </div>
                    </div>
                </div>
                <div class="main-screen-frame270">
                    <div class="main-screen-frame269">
                        <span class="main-screen-text43">Qty</span>
                    </div>
                    <img src="{{ asset('assets//external/line24388-6vb.svg')}}" alt="Line24388" class="main-screen-line2" />
                    <div class="main-screen-frame268">
                        <span class="main-screen-text44">Round Off</span>
                        <span class="main-screen-text45">₹210.00</span>
                    </div>
                    <img src="{{ asset('assets//external/line34388-4j2q.svg')}}" alt="Line34388" class="main-screen-line3" />
                    <div class="main-screen-frame267">
                        <span class="main-screen-text46">Total Payable</span>
                        <span class="main-screen-text47">₹210.00</span>
                    </div>
                </div>
                <div class="main-screen-frame278">
                    <div class="main-screen-frame275"></div>
                    <span class="main-screen-text48">Cash + UPI</span>
                    <img src="{{ asset('assets//external/right4388-azef.svg')}}" alt="right4388" class="main-screen-right2" />
                </div>
                <div class="main-screen-frame280">
                    <span class="main-screen-text49">Store Location: Warehouse</span>
                </div>
                <div class="main-screen-container2">
                    <img src="{{ asset('assets//external/systemicon16pxplus4411-k73.svg')}}" alt="systemicon16pxPlus4411"
                        class="main-screen-systemicon16px-plus1" />
                </div>
                <div class="main-screen-container3">
                    <img src="{{ asset('assets//external/systemicon16pxplus4411-cimj.svg')}}" alt="systemicon16pxPlus4411"
                        class="main-screen-systemicon16px-plus2" />
                </div>
                <div class="main-screen-container4">
                    <img src="{{ asset('assets//external/systemicon16pxplus4411-0lgn.svg')}}" alt="systemicon16pxPlus4411"
                        class="main-screen-systemicon16px-plus3" />
                </div>
                <div class="main-screen-frame271">
                    <span class="main-screen-text50">Cash</span>
                </div>
                <div class="main-screen-frame272">
                    <span class="main-screen-text51">Void Sales</span>
                </div>
                <div class="main-screen-frame273">
                    <span class="main-screen-text52">Hold</span>
                </div>
                <div class="main-screen-frame274">
                    <span class="main-screen-text53">Online</span>
                </div>
                <div class="main-screen-frame338">
                    <div class="main-screen-frame337">
                        <div class="main-screen-glyph">
                            <img src="{{ asset('assets//external/vector4385-0u7pi.svg')}}" alt="Vector4385"
                                class="main-screen-vector16" />
                            <img src="{{ asset('assets//external/frame2834385-mv16.svg')}}" alt="Frame2834385"
                                class="main-screen-frame283" />
                        </div>
                        <span class="main-screen-text54">Stock Request</span>
                    </div>
                    <img src="{{ asset('assets//external/rectangle4574471-vjb7-200h.png')}}" alt="Rectangle4574471"
                        class="main-screen-rectangle457" />
                    <div class="main-screen-frame336">
                        <div class="main-screen-frame1">
                            <div class="main-screen-layerx00201">
                                <div class="main-screen-frame1669194552080">
                                    <img src="{{ asset('assets//external/vector4411-gld9h.svg')}}" alt="Vector4411"
                                        class="main-screen-vector17" />
                                    <img src="{{ asset('assets//external/vector4411-sew.svg')}}" alt="Vector4411"
                                        class="main-screen-vector18" />
                                </div>
                            </div>
                        </div>
                        <span class="main-screen-text55">Cash Out</span>
                    </div>
                    <img src="{{ asset('assets//external/rectangle4564411-elza-200h.png')}}" alt="Rectangle4564411"
                        class="main-screen-rectangle456" />
                    <div class="main-screen-frame335">
                        <div class="main-screen-frame2">
                            <div class="main-screen-layer4">
                                <div class="main-screen-group4">
                                    <img src="{{ asset('assets//external/vector4385-nhq8.svg')}}" alt="Vector4385"
                                        class="main-screen-vector19" />
                                </div>
                            </div>
                        </div>
                        <span class="main-screen-text56">View Hold</span>
                    </div>
                    <img src="{{ asset('assets//external/rectangle4584471-wdaa-200h.png')}}" alt="Rectangle4584471"
                        class="main-screen-rectangle458" />
                    <div class="main-screen-frame334">
                        <div class="main-screen-group315">
                            <img src="{{ asset('assets//external/vector4386-t6uc.svg')}}" alt="Vector4386"
                                class="main-screen-vector20" />
                            <img src="{{ asset('assets//external/vector4386-inus.svg')}}" alt="Vector4386"
                                class="main-screen-vector21" />
                            <img src="{{ asset('assets//external/vector4386-j8ka.svg')}}" alt="Vector4386"
                                class="main-screen-vector22" />
                        </div>
                        <span class="main-screen-text57">Print Invoice</span>
                    </div>
                    <img src="{{ asset('assets//external/rectangle4604471-webg-200h.png')}}" alt="Rectangle4604471"
                        class="main-screen-rectangle460" />
                    <div class="main-screen-frame333">
                        <img src="{{ asset('assets//external/orderhistory14386-pjd.svg')}}" alt="orderhistory14386"
                            class="main-screen-orderhistory1" />
                        <span class="main-screen-text58">Sales History</span>
                    </div>
                    <img src="{{ asset('assets//external/rectangle4614471-lhtd-200h.png')}}" alt="Rectangle4614471"
                        class="main-screen-rectangle461" />
                    <div class="main-screen-frame332">
                        <img src="{{ asset('assets//external/investment114386-6oo.svg')}}" alt="investment114386"
                            class="main-screen-investment11" />
                        <span class="main-screen-text59">Collect Credit</span>
                    </div>
                    <img src="{{ asset('assets//external/rectangle4624471-m8zu-200h.png')}}" alt="Rectangle4624471"
                        class="main-screen-rectangle462" />
                    <div class="main-screen-frame331">
                        <div class="main-screen-group5">
                            <img src="{{ asset('assets//external/vector4386-b9nr.svg')}}" alt="Vector4386"
                                class="main-screen-vector23" />
                            <img src="{{ asset('assets//external/vector4386-kjdm.svg')}}" alt="Vector4386"
                                class="main-screen-vector24" />
                            <img src="{{ asset('assets//external/vector4386-a8pd.svg')}}" alt="Vector4386"
                                class="main-screen-vector25" />
                        </div>
                        <span class="main-screen-text60">Close Shift</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade bd-example-modal-lg" id="approveModal" tabindex="-1" role="dialog"
        aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="modalContent">
            </div>
        </div>
    </div>

    <script>
        setTimeout(function() {
            $('.toast').fadeOut('slow');
        }, 5000); // 5 seconds before fade-out


        $(document).on('click', '.open-form', function() {
            let type = $(this).data('type');

            let id = $(this).data('id');

            let nfid = $(this).data('nfid');
            let id_get = $(this).attr('id');

            let get_tc = parseInt($(".notification-count").text()); // get current cou

            // console.log(get_tc,"==get_tc");
            $.ajax({
                url: '/popup/form/' + type + "?id=" + id + "&nfid=" + nfid,
                type: 'GET',
                success: function(response) {
                    $("#" + id_get).removeClass("iq-sub-card open-form mb-1 msg_unread");
                    $("#" + id_get).addClass("iq-sub-card open-form mb-1 msg_read");


                    if (get_tc > 0) {
                        get_tc = get_tc - 1;
                    }
                    $(".notification-count").text(get_tc);

                    $('#modalContent').html(response);

                    $('#approveModal').modal('show');
                },
                error: function() {
                    alert('Failed to load form.');
                }
            });
        });

        // Optional: Close modal on background click
        $(document).on('click', '#popupModal', function(e) {
            if (e.target === this) {
                $(this).fadeOut();
            }
        });

        function nfModelCls() {
            $('#approveModal').modal('hide');
        }
    </script>
    @livewireScripts

</body>

</html>
