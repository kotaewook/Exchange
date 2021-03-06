<?php
// add_javascript('<script type="text/javascript"src="' . ROOT . 'public/js/balance/history.js"></script>');

$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$coin = isset($_GET['coin']) ? $_GET['coin'] : 'all';
$date = isset($_GET['date']) ? $_GET['date'] : 'all';

$dateArray = array(
    'all' => lang('모든 기간', 'All Periods', '全ての期間','百日'),
    '1m' => lang('지난 1개월', 'Last month', '過去1ヶ月','前一个月'),
    '3m' => lang('지난 3개월', 'Last three months', '過去3ヶ月','前三个月'),
    '6m' => lang('지난 6개월', 'Last six months', '過去6ヶ月','前六个月'),
    '1y' => lang('지난 1년', 'Past year', '過去1年','过去一年')
);
?>
<div class="balance-options">
    <div class="balance-select-boxes">
        <div class="select-contract">
            <div class="input-box">
                <span><?= $type == 'all' ? lang('모든 거래', 'All', '全ての取引','一手买卖') : ($type == 'buy' ? lang('구매', 'Buy',
                        '購入','采购') : lang('판매', 'Sell', '販売','销售')) ?></span>
                <input type="hidden" class="contract-selected" value="<?= $type ?>" readonly data-select="all"
                       data-name="type">
                <ul>
                    <li data-option="all"><?= lang('모든 거래', 'All', '全ての取引','一手买卖') ?></li>
                    <li data-option="buy"><?= lang('구매', 'Buy', '購入','采购') ?></li>
                    <li data-option="sell"><?= lang('판매', 'Sell', '販売','销售') ?></li>
                </ul>
            </div>
        </div>
        <div class="select-coin">
            <div class="input-box">
                <span><?= $coin == 'all' ? lang('모든 코인', 'All Coins', '全てのコイン','所有硬币') : $coin ?></span>
                <input type="hidden" class="coin-selected" value="<?= $coin ?>" readonly data-select="all"
                       data-name="coin">
                <ul>
                    <li data-option="all"><?= lang('모든 코인', 'All Coins', '全てのコイン','所有硬币') ?></li>
                    <?php
                    $db = new db();
                    $query = "SELECT ci_symbol FROM _coins WHERE ci_use = 0 ORDER BY ci_no ASC";
                    $list = $db->fetchAll($query);
                    foreach ($list as $row)
                        echo '<li data-option="' . $row->ci_symbol . '">' . $row->ci_symbol . '</li>';
                    ?>
                </ul>
            </div>
        </div>
        <div class="select-date">
            <div class="input-box">
                <span><?= $dateArray[$date] ?></span>
                <input type="hidden" class="date-selected" value="<?= $date ?>" readonly data-select="all"
                       data-name="date">
                <ul>
                    <?php
                    foreach ($dateArray as $dayting => $value)
                        echo "<li data-option='{$dayting}'>{$value}</li>";
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <a id="refreshBtn" href="javascript:location.reload()"><img src="/public/img/balance/refresh.svg"
                                                                alt="refresh"><?= lang('새로고침', 'Refresh', 'リフレッシュ','新旧') ?></a>
</div>
<div class="balance-content">
    <table class="history-table">
        <colgroup>
            <col width="12%">
            <col width="13%">
            <col width="14%">
            <col width="13%">
            <col width="15%">
            <col width="15%">
            <col width="18%">
        </colgroup>
        <thead>
        <tr>
            <th><?= lang('거래종류', 'Transaction type', '取引タイプ','交易种类') ?></th>
            <th><?= lang('코인/마켓', 'Coin/Market', 'コイン/マーケット','硬币/市场') ?></th>
            <th><?= lang('거래상태', 'Transaction status', '取引状態','交易状态') ?><span class="statusDesc"><button class="descIcon"
                                                                                                  onclick="openStatusDesc()"></button></span>
            </th>
            <th class="text-right"><?= lang('거래단가', 'Transaction unit price', '取引単価','交易单价') ?></th>
            <th class="text-right"><?= lang('거래수량', 'Transaction quantity', '取引数量','交易量') ?></th>
            <th class="text-right"><?= lang('거래금액', 'Transaction amount', '取引金額','交易金额') ?></th>
            <th><?= lang('완료시간', 'Completion time', '完了時間','完成时间') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        /* 페이지 시작지점 구하기 */
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $page = $page < 1 ? 1 : $page;
        $limits = 15;
        $fromRecord = ($page - 1) * $limits;

        /* 필터 조건 */
        $sql_where = '';
        $sql_where .= $type != 'all' ? ' AND co_type = ' . ($type == 'buy' ? 1 : 0) : '';
        $sql_where .= $coin != 'all' ? " AND co_symbol = '{$coin}'" : '';
        switch ($date) {
            case '1m':
                $sql_where .= " AND co_datetime >= '" . strtotime('-1 months') . "' AND co_datetime <= '" . time() . "' ";
                break;
            case '3m':
                $sql_where .= " AND co_datetime >= '" . strtotime('-3 months') . "' AND co_datetime <= '" . time() . "' ";
                break;
            case '6m':
                $sql_where .= " AND co_datetime >= '" . strtotime('-6 months') . "' AND co_datetime <= '" . time() . "' ";
                break;
            case '1y':
                $sql_where .= " AND co_datetime >= '" . strtotime('-1 years') . "' AND co_datetime <= '" . time() . "' ";
                break;
            default :
                break;
        }

        $db = new db();
        $query = "SELECT * FROM _c2cOrders WHERE mb_id = '{$member['mb_id']}' {$sql_where} ORDER BY co_datetime DESC, co_no DESC";
        $totalCnt = $db->fetchAll(str_replace('*', 'COUNT(*) AS cnt', $query))[0];
        $totalCnt = $totalCnt->cnt;

        $list = $db->fetchAll($query . " LIMIT {$fromRecord}, {$limits}");
        foreach ($list as $row) {
            $row = objectToArray($row);
            ?>
            <tr>
                <td><?= $row['co_type'] == 0 ? lang('판매', 'Sales', '販売','销售') : lang('구매', 'Purchase', '購入付け','采购') ?></td>
                <td><?= $row['co_symbol'] ?>/USDT</td>
                <td><?= lang('완료', 'Completion', '完了','完成') ?></td>
                <td class="text-right"><?= round_down_format($row['co_price'], 4) ?><br>USDT</td>
                <td class="text-right"><?= round_down_format($row['co_amount'], 4) ?><br><?= $row['co_symbol'] ?></td>
                <td class="text-right"><?= round_down_format($row['co_price'] * $row['co_amount'], 4) ?><br>USDT</td>
                <td><?= date('Y-m-d H:i:s', $row['co_datetime']) ?></td>
            </tr>
        <?php }
        if (count($list) == 0) { ?>
            <tr>
                <td colspan="7"
                    class="no-data"><?= lang('거래내역이 없습니다.', 'No transaction details.', '取引履歴がありません。','没有交易明细。') ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <?= paging($totalCnt, $page, $limits) ?>
</div>
</div>