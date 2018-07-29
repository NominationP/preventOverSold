# Prevent Over Sale


## Mysql

1. code running sort
```
    // mysql innodb ==> exi
    $sql = "update store set amount = amount-1 where goods_id = 12345";
    $this->db_instance->query($sql);
    $sql = "select amount from store where goods_id = 12345";
    $count = $this->db_instance->query($sql)->fetch_assoc()['amount'
```
2. all atomization 