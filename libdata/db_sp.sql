-- http://www.mysqltutorial.org/mysql-stored-procedure-tutorial.aspx

USE lobukisticker;

DELIMITER $$

-- {{{ uspResetPositions
DROP PROCEDURE IF EXISTS uspResetPositions$$
CREATE PROCEDURE uspResetPositions(IN collection VARCHAR(50))
BEGIN
    DECLARE spStatus            TINYINT;
    DECLARE done                INT DEFAULT 0;
    DECLARE tag                 VARCHAR(50);
    DECLARE category            VARCHAR(50);
    DECLARE position            TINYINT(4) DEFAULT 0;

    DECLARE productCursor CURSOR FOR
        SELECT P.tag, P.category
        FROM Products AS P
        WHERE P.collection = collection
        ORDER BY P.position;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;
    DECLARE EXIT HANDLER FOR SQLWARNING ROLLBACK;

sp:BEGIN
    START TRANSACTION;

    SET spStatus = 0;

    -- Loop over products
    OPEN productCursor;

    productLoop: LOOP
        FETCH productCursor INTO tag, category;

        IF done THEN
            LEAVE productLoop;
        END IF;

        SET position = position + 1;

        UPDATE
            Products AS P
        SET
            P.position = position
        WHERE
                P.collection = collection
            AND P.category = category
            AND P.tag = tag;

    END LOOP;

    CLOSE productCursor;

    SET spStatus = 1;

    COMMIT;

    SELECT spStatus;
END;

END$$
-- }}}

-- {{{ uspCreateOrder
DROP PROCEDURE IF EXISTS uspCreateOrder$$
CREATE PROCEDURE uspCreateOrder(
    IN orderNbr                   VARCHAR(50),
    IN basket                     INT,
    IN shippingAddressIsDifferent TINYINT(1),
    IN billingName                VARCHAR(50),
    IN billingFirstname           VARCHAR(50),
    IN billingStreet              VARCHAR(50),
    IN billingZipCode             VARCHAR(50),
    IN billingCity                VARCHAR(50),
    IN billingEmail               VARCHAR(50),
    IN billingPhone               VARCHAR(50),
    IN shippingName               VARCHAR(50),
    IN shippingFirstname          VARCHAR(50),
    IN shippingStreet             VARCHAR(50),
    IN shippingZipCode            VARCHAR(50),
    IN shippingCity               VARCHAR(50),
    IN shippingEmail              VARCHAR(50),
    IN shippingPhone              VARCHAR(50)
)
BEGIN
    DECLARE spStatus            TINYINT;
    DECLARE basketExists        INT;
    DECLARE countLines          INT;
    DECLARE done                INT DEFAULT 0;
    DECLARE oldOrderNbr         VARCHAR(50);
    DECLARE productsPrice       DECIMAL(6, 2);
    DECLARE shippingPrice       DECIMAL(6, 2);
    DECLARE lineId              INT;
    DECLARE lineCreationTime    DATETIME;
    DECLARE lineBasket          INT;
    DECLARE lineCollection      VARCHAR(50);
    DECLARE lineCategory        VARCHAR(50);
    DECLARE lineProduct         VARCHAR(50);
    DECLARE lineUnitPrice       INT;
    DECLARE lineQtity           INT;

    DECLARE basketCursor CURSOR FOR
        SELECT BL.*
        FROM   BasketLines AS BL
        WHERE  BL.basket = basket;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;
    DECLARE EXIT HANDLER FOR SQLWARNING ROLLBACK;

sp:BEGIN
    SELECT count(B.id) INTO basketExists
    FROM   Baskets AS B
    WHERE  B.id = basket;

    -- The basket does not exist
    IF basketExists = 0 THEN
        SET spStatus = -2;

        LEAVE sp;
    END IF;

    SELECT COUNT(BL.id) INTO countLines
    FROM   BasketLines AS BL
    WHERE  BL.basket = basket;

    -- If there is nothing in the basket, do nothing
    IF countLines = 0 THEN
        SET spStatus = -1;

        LEAVE sp;
    END IF;

    START TRANSACTION;

    SET spStatus = 0;

    SELECT B.orderNbr INTO oldOrderNbr
    FROM   Baskets AS B
    WHERE  B.id = basket LIMIT 1;

    -- Delete previous order
    DELETE FROM Orders
    WHERE Orders.orderNbr = oldOrderNbr;

    DELETE FROM OrderLines
    WHERE OrderLines.orderNbr = oldOrderNbr;

    -- Loop over items in the basket to create order items
    OPEN basketCursor;

    basketLoop: LOOP
        FETCH basketCursor INTO
            lineId, lineCreationTime, lineBasket, lineCollection,
            lineCategory, lineProduct, lineUnitPrice, lineQtity;

        IF done THEN
            LEAVE basketLoop;
        END IF;

        INSERT INTO OrderLines
        (creationTime, orderNbr, collection, category, product,
            unitPrice, qtity)
        VALUES
        (NOW(), orderNbr, lineCollection, lineCategory, lineProduct,
            lineUnitPrice, lineQtity);
    END LOOP;

    CLOSE basketCursor;

    -- Compute products' price
    SELECT SUM(OL.qtity * OL.unitPrice) INTO productsPrice
    FROM   OrderLines AS OL
    WHERE  OL.orderNbr = orderNbr;

    -- Compute shipping price
    SELECT
        MAX(P.shippingPrice) INTO shippingPrice
    FROM OrderLines AS OL
        INNER JOIN Products AS P
            ON OL.collection = P.collection
                AND OL.category = P.category
                AND OL.product = P.tag
    WHERE
        OL.orderNbr = orderNbr;

    INSERT INTO `Orders` VALUES
    (orderNbr,
        shippingAddressIsDifferent,
        billingName,
        billingFirstname,
        billingStreet,
        billingZipCode,
        billingCity,
        billingEmail,
        billingPhone,
        shippingName,
        shippingFirstname,
        shippingStreet,
        shippingZipCode,
        shippingCity,
        shippingEmail,
        shippingPhone,
        productsPrice,
        shippingPrice,
        productsPrice + shippingPrice,
        NOW(),
        DEFAULT,
        DEFAULT);

    SET spStatus = 1;

    COMMIT;

    IF spStatus = 1 THEN
        UPDATE Baskets SET orderNbr = orderNbr WHERE id = basket;
    END IF;
END;

    IF spStatus = 1 THEN
        SELECT * FROM Orders AS O WHERE O.orderNbr = orderNbr;
        SELECT
            OL.id         AS id,
            P.title       AS name,
            OL.qtity      AS qtity,
            OL.unitPrice  AS unitPrice
        FROM
            OrderLines AS OL
                INNER JOIN Products AS P
                    ON OL.category = P.category
                        AND OL.collection = P.collection
                        AND OL.product = P.tag
        WHERE
            OL.orderNbr = orderNbr;
    END IF;
END$$
-- }}}

-- {{{ uspAddToBasket
DROP PROCEDURE IF EXISTS uspAddToBasket$$
CREATE PROCEDURE uspAddToBasket(
    IN basket       INT,
    IN collection   VARCHAR(50),
    IN category     VARCHAR(50),
    IN product      VARCHAR(50),
    IN qtity        INT,
    IN unitPrice    DECIMAL(6, 2))
BEGIN
    DECLARE basketExists INT;

sp:BEGIN
    SELECT count(B.id) INTO basketExists
    FROM   Baskets AS B
    WHERE  B.id = basket;

    -- The basket does not exist
    IF basketExists = 0 THEN
        SELECT -1;

        LEAVE sp;
    END IF;

    INSERT INTO BasketLines
    (creationTime, basket, collection, category, product, qtity, unitPrice)
    VALUES
    (NOW(), basket, collection, category, product, qtity, unitPrice);

    SELECT COUNT(BL.id)
    FROM   BasketLines AS BL
    WHERE  BL.basket = basket;
END;
END$$
-- }}}
-- {{{ uspFindBasketLines
DROP PROCEDURE IF EXISTS uspFindBasketLines$$
CREATE PROCEDURE uspFindBasketLines(IN basket INT)
BEGIN
    SELECT
        BL.*
    FROM
        BasketLines AS BL
            INNER JOIN Baskets AS B
                ON BL.basket = B.id
    WHERE
        B.id = basket;
END$$
-- }}}
-- {{{ uspFindBasketOrder
DROP PROCEDURE IF EXISTS uspFindBasketOrder$$
CREATE PROCEDURE uspFindBasketOrder(IN basket INT)
BEGIN
    SELECT
        O.*
    FROM Orders AS O
        INNER JOIN Baskets AS B
            ON O.orderNbr = B.orderNbr
    WHERE
        B.id = basket
    ORDER BY
        O.creationTime DESC
    LIMIT 1;
END$$
-- }}}
-- {{{ uspFindBasketProducts
DROP PROCEDURE IF EXISTS uspFindBasketProducts$$
CREATE PROCEDURE uspFindBasketProducts(IN basket INT)
BEGIN
    SELECT
        BL.qtity,
        BL.unitPrice,
        P.*
    FROM
        BasketLines AS BL
            INNER JOIN Baskets AS B
                ON BL.basket = B.id
            INNER JOIN Products AS P
                ON BL.category = P.category
                    AND BL.collection = P.collection
                    AND BL.product = P.tag
    WHERE
        B.id = basket;
END$$
-- }}}
-- {{{ uspRemoveFromBasket
DROP PROCEDURE IF EXISTS uspRemoveFromBasket$$
CREATE PROCEDURE uspRemoveFromBasket(
    IN basket     INT,
    IN collection VARCHAR(50),
    IN category   VARCHAR(50),
    IN product    VARCHAR(50))
BEGIN
    DECLARE basketExists INT;

sp:BEGIN
    SELECT count(B.id) INTO basketExists
    FROM   Baskets AS B
    WHERE  B.id = basket;

    -- The basket does not exist
    IF basketExists = 0 THEN
        SELECT -1;

        LEAVE sp;
    END IF;

    DELETE FROM BasketLines
    WHERE
            BasketLines.basket = basket
        AND BasketLines.collection = collection
        AND BasketLines.category = category
        AND BasketLines.product = product;

    SELECT COUNT(BL.id)
    FROM   BasketLines AS BL
    WHERE  BL.basket = basket;
END;
END$$
-- }}}
-- {{{ uspUpdateBasket
DROP PROCEDURE IF EXISTS uspUpdateBasket$$
CREATE PROCEDURE uspUpdateBasket(
    IN qtity      INT,
    IN basket     INT,
    IN collection VARCHAR(50),
    IN category   VARCHAR(50),
    IN product    VARCHAR(50))
BEGIN
    DECLARE basketExists INT;

sp:BEGIN
    SELECT count(B.id) INTO basketExists
    FROM   Baskets AS B
    WHERE  B.id = basket;

    -- The basket does not exist
    IF basketExists = 0 THEN
        SELECT -1;

        LEAVE sp;
    END IF;

    UPDATE
        BasketLines AS BL
    SET
        BL.qtity = qtity
    WHERE
            BL.basket = basket
        AND BL.collection = collection
        AND BL.category = category
        AND BL.product = product;

    SELECT COUNT(BL.id)
    FROM   BasketLines AS BL
    WHERE  BL.basket = basket;
END;
END$$
-- }}}

-- {{{ uspCountProductsInCategory
DROP PROCEDURE IF EXISTS uspCountProductsInCategory$$
CREATE PROCEDURE uspCountProductsInCategory(
    IN collection VARCHAR(50), IN category VARCHAR(50))
BEGIN
    SELECT
        COUNT(*)
    FROM
        Products AS P
            INNER JOIN Collections AS C
                ON P.category = C.tag AND P.collection = C.parent
    WHERE
            P.collection = collection
        AND P.category = category
        AND P.enabled = '1'
        AND C.enabled = '1';
END$$
-- }}}
-- {{{ uspCountProductsInCollection
DROP PROCEDURE IF EXISTS uspCountProductsInCollection$$
CREATE PROCEDURE uspCountProductsInCollection(IN collection VARCHAR(50))
BEGIN
    SELECT
        COUNT(*)
    FROM
        Products AS P
            INNER JOIN Collections AS C
                ON P.category = C.tag AND P.collection = C.parent
    WHERE
            P.collection = collection
        AND P.enabled = '1'
        AND C.enabled = '1';
END$$
-- }}}
-- {{{ uspFindAllProducts
DROP PROCEDURE IF EXISTS uspFindAllProducts$$
CREATE PROCEDURE uspFindAllProducts()
BEGIN
    SELECT P.* FROM Products AS P WHERE P.enabled='1';
END$$
-- }}}
-- {{{ uspFindAllProductsInCategory
DROP PROCEDURE IF EXISTS uspFindAllProductsInCategory$$
CREATE PROCEDURE uspFindAllProductsInCategory(
    IN collection VARCHAR(50), IN category VARCHAR(50))
BEGIN
    SELECT
        P.*
    FROM
        Products AS P
            INNER JOIN Collections AS C
                ON P.category = C.tag AND P.collection = C.parent
    WHERE
            P.collection = collection
        AND P.category = category
        AND P.enabled = '1'
        AND C.enabled = '1'
    ORDER BY
        P.position DESC,
        P.creationDate DESC;
END$$
-- }}}
-- {{{ uspFindAllProductsInCollection
DROP PROCEDURE IF EXISTS uspFindAllProductsInCollection$$
CREATE PROCEDURE uspFindAllProductsInCollection(IN collection VARCHAR(50))
BEGIN
    SELECT
        P.*
    FROM
        Products AS P
            INNER JOIN Collections AS C
                ON P.category = C.tag AND P.collection = C.parent
    WHERE
            P.collection=collection
        AND P.enabled = '1'
        AND C.enabled = '1'
    ORDER BY
        P.position DESC;
END$$
-- }}}
-- {{{ uspFindCategories
DROP PROCEDURE IF EXISTS uspFindCategories$$
CREATE PROCEDURE uspFindCategories()
BEGIN
    SELECT
        *
    FROM
        Collections
    WHERE
            enabled = '1'
        AND parent <> ''
    ORDER BY
        position ASC;
END$$
-- }}}
-- {{{ uspFindCategoriesInCollection
DROP PROCEDURE IF EXISTS uspFindCategoriesInCollection$$
CREATE PROCEDURE uspFindCategoriesInCollection(IN collection VARCHAR(50))
BEGIN
    SELECT
        *
    FROM
        Collections
    WHERE
            parent = collection
        AND enabled = '1'
    ORDER BY
        position ASC;
END$$
-- }}}
-- {{{ uspFindCategory
DROP PROCEDURE IF EXISTS uspFindCategory$$
CREATE PROCEDURE uspFindCategory(
    IN collection VARCHAR(50), IN category VARCHAR(50))
BEGIN
    SELECT
        C.*,
        CO.description  AS co_description,
        CO.enabled      AS co_enabled,
        CO.parent       AS co_parent,
        CO.position     AS co_position,
        CO.shortTitle   AS co_shortTitle,
        CO.tag          AS co_tag,
        CO.title        AS co_title
    FROM
        Collections AS C
            INNER JOIN Collections AS CO
                ON C.parent = CO.tag
    WHERE
            C.parent = collection
        AND C.tag = category
        AND C.enabled = '1'
        AND CO.enabled = '1';
END$$
-- }}}
-- {{{ uspFindCategoryWithProducts
DROP PROCEDURE IF EXISTS uspFindCategoryWithProducts$$
CREATE PROCEDURE uspFindCategoryWithProducts(
    IN collection VARCHAR(50),
    IN category   VARCHAR(50),
    IN startIndex INT,
    IN pageCount  INT
)
BEGIN
    -- Category
    SELECT
        C.*,
        CO.description  AS co_description,
        CO.enabled      AS co_enabled,
        CO.parent       AS co_parent,
        CO.position     AS co_position,
        CO.shortTitle   AS co_shortTitle,
        CO.tag          AS co_tag,
        CO.title        AS co_title
    FROM
        Collections AS C
            INNER JOIN Collections AS CO
                ON C.parent = CO.tag
    WHERE
            C.parent = collection
        AND C.tag = category
        AND C.enabled = '1'
        AND CO.enabled = '1';

    -- Products in Category
    PREPARE STMT FROM
    "SELECT P.* FROM Products AS P
        INNER JOIN Collections AS C
            ON P.category = C.tag AND P.collection = C.parent
    WHERE
            P.collection = ? AND P.category = ?
        AND P.enabled = '1' AND C.enabled = '1'
    ORDER BY P.position DESC
    LIMIT ?, ?";
    SET @collection = collection;
    SET @category   = category;
    SET @startIndex = startIndex;
    SET @pageCount  = pageCount;
    EXECUTE STMT USING @collection, @category, @startIndex, @pageCount;
END$$
-- }}}
-- {{{ uspFindCollection
DROP PROCEDURE IF EXISTS uspFindCollection$$
CREATE PROCEDURE uspFindCollection(IN tag VARCHAR(50))
BEGIN
    SELECT
        *
    FROM
        Collections AS C
    WHERE
            C.tag = tag
        AND C.enabled = '1'
        AND C.parent = '';
END$$
-- }}}
-- {{{ uspFindCollections
DROP PROCEDURE IF EXISTS uspFindCollections$$
CREATE PROCEDURE uspFindCollections()
BEGIN
    SELECT
        *
    FROM
        Collections
    WHERE
            enabled = '1'
        AND parent = ''
    ORDER BY
        position ASC;
END$$
-- }}}
-- {{{ uspFindNewProducts
DROP PROCEDURE IF EXISTS uspFindNewProducts$$
CREATE PROCEDURE uspFindNewProducts()
BEGIN
    SELECT
        P.*
    FROM
        Products AS P
    WHERE
            P.isNew = '1'
        AND P.enabled = '1'
    ORDER BY
        P.creationDate DESC;
END$$
-- }}}
-- {{{ uspFindProduct
DROP PROCEDURE IF EXISTS uspFindProduct$$
CREATE PROCEDURE uspFindProduct(
    IN collection VARCHAR(50), IN category VARCHAR(50),
    IN tag VARCHAR(50))
BEGIN
    SELECT
        P.*
    FROM
        Products AS P
            INNER JOIN Collections AS C
                ON P.category = C.tag AND P.collection = C.parent
    WHERE
            P.collection = collection
        AND P.category = category
        AND P.tag = tag
        AND P.enabled = '1'
        AND C.enabled = '1';
END$$
-- }}}
-- http://bugs.mysql.com/bug.php?id=11918
-- {{{ uspFindProductsInCategory
DROP PROCEDURE IF EXISTS uspFindProductsInCategory$$
CREATE PROCEDURE uspFindProductsInCategory(
    IN collection VARCHAR(50),
    IN category VARCHAR(50),
    IN startIndex INT,
    IN pageCount INT)
BEGIN
    PREPARE STMT FROM
    "SELECT P.* FROM Products AS P
        INNER JOIN Collections AS C
            ON P.category = C.tag AND P.collection = C.parent
    WHERE
            P.collection = ? AND P.category = ?
        AND P.enabled = '1' AND C.enabled = '1'
    ORDER BY P.position DESC, P.creationDate DESC
    LIMIT ?, ?";
    SET @collection = collection;
    SET @category   = category;
    SET @startIndex = startIndex;
    SET @pageCount  = pageCount;
    EXECUTE STMT USING @collection, @category, @startIndex, @pageCount;
END$$
-- }}}
-- {{{ uspFindProductsInCollection
DROP PROCEDURE IF EXISTS uspFindProductsInCollection$$
CREATE PROCEDURE uspFindProductsInCollection(
    IN collection VARCHAR(50), IN startIndex INT, IN pageCount INT)
BEGIN
    PREPARE STMT FROM
    "SELECT P.* FROM Products AS P
        INNER JOIN Collections AS C
            ON P.category = C.tag AND P.collection = C.parent
    WHERE
            P.collection = ?
        AND P.enabled = '1' AND C.enabled = '1'
    ORDER BY P.position DESC
    LIMIT ?, ?";
    SET @collection = collection;
    SET @startIndex = startIndex;
    SET @pageCount = pageCount;
    EXECUTE STMT USING @collection, @startIndex, @pageCount;
END$$
-- }}}

DELIMITER ;

