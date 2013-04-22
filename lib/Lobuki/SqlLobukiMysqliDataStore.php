<?php
/// Author: Pascal Tran-Ngoc-Bich <chtoucas@narvalo.org>

require_once 'Narvalo.php';

// =============================================================================

//namespace Lobuki\Persistence;

/* {{{ SqlLobukiMysqliDataStore */

class SqlLobukiMysqliDataStore
    extends MysqliDataStore
    implements ILobukiDataStore {

    public function createBasket() {
        $sql = 'INSERT INTO Baskets (creationTime) VALUES(NOW())';
        $this->query($sql);

        return $this->lastInsertId();
    }

    public function createOrder($_order_) {
        throw new Exception('TODO: Not implemented');
    }

    public function findBasketOrder($_basket_) {
        throw new Exception('TODO: Not implemented');
    }

    public function findBasketLines($_basket_) {
        $q =<<<EOSQL
SELECT BL.* FROM BasketLines AS BL
INNER JOIN Baskets AS B ON BL.basket = B.id WHERE B.id='%s'
EOSQL;

        $sql = sprintf($q, $this->quote($_basket_));

        $basket = array();

        $result = $this->query($sql);
        while ($line = $result->fetch_object('BasketLine')) {
            $basket[Basket::Key($line)] = $line;
        }
        $result->free();

        return $basket;
    }

    public function findBasketProducts($_basket_) {
        $q =<<<EOSQL
SELECT BL.qtity, BL.unitPrice, P.* FROM BasketLines AS BL
INNER JOIN Baskets AS B ON BL.basket = B.id
INNER JOIN Products AS P ON BL.category = P.category
AND BL.collection = P.collection AND BL.product = P.tag
WHERE B.id='%s'
EOSQL;

        $sql = sprintf($q, $this->quote($_basket_));

        $products = array();

        $result = $this->query($sql);
        while ($product = $result->fetch_object('BasketProduct')) {
            $products[] = $product;
        }
        $result->free();

        return $products;
    }

    public function addToBasket($_basket_, $_collection_,
        $_category_, $_product_, $_qtity_, $_price_) {
        $q =<<<EOSQL
INSERT INTO BasketLines (creationTime,basket,collection,category,product,qtity,unitPrice)
VALUES(NOW(),%s,'%s','%s','%s','%s','%s');
SELECT COUNT(id) FROM BasketLines WHERE basket = '%s'
EOSQL;
        $b = $this->quote($_basket_);
        $sql = sprintf($q,
            $b,
            $this->quote($_collection_),
            $this->quote($_category_),
            $this->quote($_product_),
            $this->quote($_qtity_),
            $this->quote($_price_),
            $b);

        $this->multiQuery($sql);
        $this->nextResult();
        $result = $this->storeResult();
        $row = $result->fetch_row();
        $count = $row[0];
        $result->free();

        return $count;
    }

    public function updateBasket($_basket_, $_collection_,
        $_category_, $_product_, $_qtity_) {
        $q =<<<EOSQL
UPDATE BasketLines SET qtity = %s
WHERE basket = %s AND collection='%s' AND category = '%s'
AND product = '%s';
SELECT COUNT(id) FROM BasketLines WHERE basket = '%s'
EOSQL;
        $b = $this->quote($_basket_);
        $sql = sprintf($q,
            $this->quote($_qtity_),
            $b,
            $this->quote($_collection_),
            $this->quote($_category_),
            $this->quote($_product_),
        $b);

        $this->multiQuery($sql);
        $this->nextResult();
        $result = $this->storeResult();
        $row = $result->fetch_row();
        $count = $row[0];
        $result->free();

        return $count;
    }

    public function removeFromBasket($_basket_, $_collection_,
        $_category_, $_product_) {
        $q =<<<EOSQL
DELETE FROM BasketLines
WHERE basket = %s AND collection='%s' AND category = '%s'
AND product = '%s';
SELECT COUNT(id) FROM BasketLines WHERE basket = '%s'
EOSQL;
        $b = $this->quote($_basket_);
        $sql = sprintf($q,
            $b,
            $this->quote($_collection_),
            $this->quote($_category_),
            $this->quote($_product_),
            $b);

        $this->multiQuery($sql);
        $this->nextResult();
        $result = $this->storeResult();
        $row = $result->fetch_row();
        $count = $row[0];
        $result->free();

        return $count;
    }

    // \return int
    public function countProductsInCategory($_collection_, $_category_) {
        $q =<<<EOSQL
SELECT COUNT(*) FROM Products AS P
INNER JOIN Collections AS C ON P.category = C.tag AND P.collection = C.parent
WHERE P.collection='%s' AND P.category='%s' AND P.enabled='1' AND C.enabled = '1'
EOSQL;
        $c = $this->quote($_category_);
        $sql = sprintf($q, $this->quote($_collection_), $c, $c);

        $result = $this->query($sql);
        $row = $result->fetch_row();
        $count = $row[0];
        $result->free();

        return $count;
    }

    // \return int
    public function countProductsInCollection($_collection_) {
        $q =<<<EOSQL
SELECT COUNT(*) FROM Products AS P
INNER JOIN Collections AS C ON P.category = C.tag AND P.collection = C.parent
WHERE P.collection='%s' AND P.enabled='1' AND C.enabled = '1'
EOSQL;

        $sql = sprintf($q, $this->quote($_collection_));

        $result = $this->query($sql);
        $row = $result->fetch_row();
        $count = $row[0];
        $result->free();

        return $count;
    }

    // \return array of Product
    public function findAllProducts() {
        $sql =<<<EOSQL
SELECT P.* FROM Products AS P WHERE P.enabled='1'
EOSQL;

        $products = array();

        $result = $this->query($sql);
        while ($product = $result->fetch_object('Product')) {
            $products[] = $product;
        }
        $result->free();

        return $products;
    }

    // \return array of Product
    public function findAllProductsInCategory($_collection_, $_category_) {
        // XXXXXXXXXXXXXXXXXXXXX
        $q =<<<EOSQL
SELECT P.* FROM Products AS P
INNER JOIN Collections AS C ON P.category = C.tag AND P.collection = C.parent
WHERE P.collection='%s' AND P.category='%s' AND P.enabled='1' AND C.enabled = '1'
ORDER BY P.position DESC
EOSQL;

        $c = $this->quote($_category_);
        $sql = sprintf($q, $this->quote($_collection_), $c, $c);

        $products = array();

        $result = $this->query($sql);
        while ($product = $result->fetch_object('Product')) {
            $products[] = $product;
        }
        $result->free();

        return $products;
    }

    // \return array of Product
    public function findAllProductsInCollection($_collection_) {
        $q =<<<EOSQL
SELECT P.* FROM Products AS P
INNER JOIN Collections AS C ON P.category = C.tag AND P.collection = C.parent
WHERE P.collection='%s' AND P.enabled='1' AND C.enabled = '1'
ORDER BY P.creationdate DESC
EOSQL;

        $sql = sprintf($q, $this->quote($_collection_));

        $products = array();

        $result = $this->query($sql);
        while ($product = $result->fetch_object('Product')) {
            $products[] = $product;
        }
        $result->free();

        return $products;
    }

    // \return array of Category's
    public function findCategories() {
        $sql =<<<EOSQL
SELECT * FROM Collections WHERE enabled='1' AND parent<>''
ORDER BY position ASC
EOSQL;

        $categories = array();

        $result = $this->query($sql);
        while ($category = $result->fetch_object('Category')) {
            $parent = $category->parent;
            if (array_key_exists($parent, $categories)) {
                $categories[$parent][] = $category;
            } else {
                $categories[$parent] = array($category);
            }
        }
        $result->free();

        return $categories;
    }

    // \return array of Category
    public function findCategoriesInCollection($_collection_) {
        $q =<<<EOSQL
SELECT * FROM Collections WHERE parent='%s' AND enabled='1' ORDER BY position ASC
EOSQL;

        $sql = sprintf($q, $this->quote($_collection_));

        $categories = array();

        $result = $this->query($sql);
        while ($category = $result->fetch_object('Category')) {
            $categories[] = $category;
        }
        $result->free();

        return $categories;
    }

    // \return Category
    public function findCategory($_collection_, $_category_) {
        $q =<<<EOSQL
SELECT
C.*,
CO.description AS co_description,
CO.enabled AS co_enabled,
CO.parent AS co_parent,
CO.position AS co_position,
CO.shortTitle as co_shortTitle,
CO.tag AS co_tag,
CO.title AS co_title
FROM Collections AS C INNER JOIN Collections AS CO ON C.parent = CO.tag
WHERE C.parent='%s' AND C.tag='%s' AND C.enabled='1' AND CO.enabled='1'
EOSQL;

        $sql = sprintf($q, $this->quote($_collection_), $this->quote($_category_));

        $result = $this->query($sql);
        $row = $result->fetch_assoc();

        if (NULL === $row) {
            $result->free();
            return NULL;
        }

        $category = new Category();
        $category->description = $row['description'];
        $category->enabled     = '1' === $row['enabled'];
        $category->parent      = $row['parent'];
        $category->position    = $row['position'];
        $category->shortTitle  = $row['shortTitle'];
        $category->tag         = $row['tag'];
        $category->title       = $row['title'];

        $collection = new Collection();
        $collection->description = $row['co_description'];
        $collection->enabled     = '1' === $row['co_enabled'];
        $collection->parent      = $row['co_parent'];
        $collection->position    = $row['co_position'];
        $collection->shortTitle  = $row['co_shortTitle'];
        $collection->tag         = $row['co_tag'];
        $collection->title       = $row['co_title'];

        $category->collection = $collection;

        $result->free();

        return $category;
    }

    // \return Collection
    public function findCollection($_tag_) {
        $q =<<<EOSQL
SELECT * FROM Collections WHERE tag='%s' AND enabled='1' AND parent=''
EOSQL;

        $sql = sprintf($q, $this->quote($_tag_));

        $result = $this->query($sql);
        $collection = $result->fetch_object('Collection');
        $result->free();

        return $collection;
    }

    // \return array of Collection
    public function findCollections() {
        $sql =<<<EOSQL
SELECT * FROM Collections WHERE enabled='1' AND parent='' ORDER BY position ASC
EOSQL;

        $collections = array();

        $result = $this->query($sql);
        while ($collection = $result->fetch_object('Collection')) {
            $collections[] = $collection;
        }
        $result->free();

        return $collections;
    }

    // \return Product
    public function findProduct($_collection_, $_category_, $_tag_) {
        $q =<<<EOSQL
SELECT P.* FROM Products AS P
INNER JOIN Collections AS C ON P.category = C.tag AND P.collection = C.parent
WHERE P.collection ='%s' AND P.category='%s' AND P.tag='%s'
AND P.enabled='1' AND C.enabled='1'
EOSQL;
        $c = $this->quote($_category_);
        $sql = sprintf($q,
            $this->quote($_collection_),
            $c,
            $c,
            $this->quote($_tag_));

        $result = $this->query($sql);
        $product = $result->fetch_object('Product');
        $result->free();

        if (NULL === $product) {
            return NULL;
        }

        if ('' !== $product->sharedImageTag) {
            $product->sharedImage = new SharedImage(
                $product->collection,
                $product->sharedImageTag
            );
        }

        return $product;
    }

    // \return array of Product
    public function findNewProducts() {
        // NB: The product and the corresponding collection must be enabled
        $sql =<<<EOSQL
SELECT P.* FROM Products AS P WHERE P.isNew='1' AND P.enabled='1' ORDER BY P.creationdate DESC
EOSQL;

        $products = array();

        $result = $this->query($sql);
        while ($product = $result->fetch_object('Product')) {
            $products[] = $product;
        }
        $result->free();

        return $products;
    }

    // \return array of Product
    public function findProductsInCategory($_collection_, $_category_, $_startIndex_, $_pageCount_) {
        // NB: The product and the corresponding collection must be enabled
        $q =<<<EOSQL
SELECT P.* FROM Products AS P
INNER JOIN Collections AS C ON P.category = C.tag AND P.collection = C.parent
WHERE P.collection='%s' AND P.category='%s' AND P.enabled='1' AND C.enabled = '1'
ORDER BY P.position DESC
LIMIT %d, %d
EOSQL;

        $sql = sprintf($q,
            $this->quote($_collection_),
            $this->quote($_category_),
            $this->quote($_startIndex_),
            $this->quote($_pageCount_));

        $products = array();

        $result = $this->query($sql);
        while ($product = $result->fetch_object('Product')) {
            $products[] = $product;
        }
        $result->free();

        return $products;
    }

    // \return array of Product
    public function findProductsInCollection($_collection_, $_startIndex_, $_pageCount_) {
        // NB: The product and the corresponding collection must be enabled
        $q =<<<EOSQL
SELECT P.* FROM Products AS P
INNER JOIN Collections AS C ON P.category = C.tag AND P.collection = C.parent
WHERE P.collection='%s' AND P.enabled='1' AND C.enabled = '1'
ORDER BY P.creationdate DESC
LIMIT %d, %d
EOSQL;

        $sql = sprintf($q,
            $this->quote($_collection_),
            $this->quote($_startIndex_),
            $this->quote($_pageCount_));

        $products = array();

        $result = $this->query($sql);
        while ($product = $result->fetch_object('Product')) {
            $products[] = $product;
        }
        $result->free();

        return $products;
    }
}

/* }}} */

// EOF

