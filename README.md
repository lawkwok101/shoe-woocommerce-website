# My E-commerce Developer Experience

Many years ago I was tasked to build a website for a shoe company that previously only sold wholesale. It was a great opportunity to learn all the facets of e-commerce including, web development, marketing, shipping, and order/inventory management.

```
**Things I Learned**

- Web: HTML/CSS, JavaScript, PHP, REST APIs
- E-commerce: WordPress/WooCommerce
- Design: Bootstrap, responsive design, A/B testing
- Database: MySQL
- Performance: Caching, CDN, Gzip Compression
- Marketing: SEO, Google Ads, Amazon Marketplace, Mailchimp
```

This repository is for my reference and future employers who want to read about design decisions and challenges I faced while building the store.

> **Note**\
The website in question no longer uses WooCommerce and the code has been slightly altered for security purposes. Permission was granted to post the code.


---

<img alt="Shoe product page" src="https://user-images.githubusercontent.com/1920793/230687079-126b5aae-1a7e-46b6-89ca-8508f19bf1b1.jpg" width="500">

---

## Adding Colour Swatches to Product Pages - `color-dropdown.php`

<img width="416" alt="Colour and size variations" src="https://user-images.githubusercontent.com/1920793/230742809-e61cc26e-f06d-4ed8-80ef-811eec2c6635.png">

## Goal

Put simply, we wanted customers to see what colours and shoe sizes were available at a glance.

As you read on, you'll discover this required working around the limitations of the e-commerce platform and making website architecture choices that would affect how products would be displayed on category pages and how their URLs appeared.

### Why custom colour swatches?

#### Reason #1 - Colour swatches are easier to understand
By default, WooCommerce uses dropdowns for variations, which is not what most people are accustomed to when shopping for shoes. Swatches are easier to understand than a dropdown list of text so we built a custom variation display.

By using swatches instead of a dropdown, customers could see all the available options and which were out of stock. Out-of-stock colours and sizes were denoted with a strikethrough and grey tint, respectively.


#### Reason 2 - We wanted dynamic stock status
While testing WooCommerce's variation system, we noticed its stock status would only appear after all variations were selected.

For example, if a customer wanted `Product A` - `Red` - `Size 41`, but it was out of stock, selecting `Red` from the dropdown would not disable the selection of `Size 41`. They would need to also select `Size 41` before an "out of stock" label appeared.

After more digging, it turns out that for performance reasons, products with over 30 variation combinations do not support dynamic variations.

> **Note:**\
Now WooCommerce has a filter hook that can modify this limitation.
https://woocommerce.com/document/change-limit-on-number-of-variations-for-dynamic-variable-product-dropdowns/

With each shoe having typically seven size variations (e.g. Size 40 - Size 46), any style that had more than 5 colours would already exceed the 30 variations limit.

## Solution
To keep each product's variation count under 30, we could treat each colour variation, or colourway, as a unique product ID with its **own product page**. By doing this, each product only had around seven variations and could, therefore, display dynamic stock status.

When the customer loaded a page, WooCommerce would include the stock status for each size variation in the HTML. We then displayed this data as size selection boxes using CSS.

Of course, this was not the only reason why we avoided lumping all colourways as variations in a monolithic product page. There were many other advantages listed below.

#### Advantages of separate product pages instead of a monolithic one:
- Keeping the number of variations per product under 30 allowed us to display out-of-stock sizes without user interaction.
- Separate product pages make low product count categories appear less bare.
- Separate product pages mean prettier URLs with fewer query parameters.
- Each page can be crawled and contain unique descriptions, title tags, and meta descriptions.
- The KISS principle. Our product input file listed each colourway as a separate SKU.

## Implementation
### Finding all the colourways
Displaying colourways of a particular shoe style was accomplished by querying the database for products with the same "style code" and displaying each result as a swatch on the product page. If there were multiple colours of a certain style of shoe, their SKUs would all share the same style code.


Each SKU follows this convention: 

`Style code` - `Material code` - `Colour code`

A SKU might look like this: `12345-67-890`


### Filling the circular swatches with colour

> **Note**\
If I were to change refactor, I would probably add the colour as a product tag or metadata to the product so we could avoid all the string manipulation and array comparisons. It would solve both issues mentioned below. However, we did have page caching so we didn't need to call the `get_main_color()` function on every page load.

The swatches were styled with CSS and not image thumbnails so we could keep product pages minimal, reduce page load times, and not have to generate thumbnails.

#### Issue #1 - Inconsistent colour codes
Unlike style codes, one colour code might refer to multiple colours so it was not a reliable way to determine the colour of the shoe. For example, one year, the colour code "`890`" could refer to "white", and the next year, "red".

How could we determine what colour the shoe actually was?

#### Solution
Thankfully, whenever we created a product, the generated title would be "`Product Name (Colour)`". The product title never changed, so we could use whatever was between the parentheses to determine the shoe colour.


#### Issue #2 - Ambiguous colour names
Now we had a colour name, but what HEX code do we actually display? How could a computer know that `khaki`, `mud`, and `bark` all mean "brown"?

#### Solution
What we ended up doing is storing these "secondary colours" in an array with key/values of `"main colour" => "secondary colour"`. By checking this array for the secondary colour "khaki", we could return the "main colour" CSS class `brown`.

Keeping track of these main-colour-to-secondary-colour relationships proved useful. We ended up using this information to build the filter options on category pages. Customers were able to find all "brownish" shoes by clicking on "Brown".

---

Thank you for reading. I'll continually update this repo and document more of my reflections and design decisions.
