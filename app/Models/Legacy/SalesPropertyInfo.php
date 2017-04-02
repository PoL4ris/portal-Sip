<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class SalesPropertyInfo extends Model {

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'old-portal';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'salesPropertyInfo';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'SalesID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['Name', 'Nickname', 'Code', 'Type', 'Market', 'Street', 'City', 'State', 'Zip', 'Neighborhood', 'ShortName', 'ContactName', 'ContactPhone', 'ContactEmail', 'Webpage', 'MgmtCo', 'PropComments', 'BuiltDate', 'Floors', 'Units', 'Mgr_Name', 'Mgr_Tel', 'Mgr_Email', 'TV_Wiring', 'TV_Provider', 'TV_BulkRetail', 'TV_ContractExpire', 'TV_Details', 'TV_Price', 'INT_Wiring', 'INT_Provider', 'INT_BulkRetail', 'INT_UnderContract', 'INT_ContractExpire', 'INT_Details', 'INT_Price', 'Phone_Wiring', 'SalesComments', 'AccountRep', 'Priority', 'Status', 'Stage', 'Probability', 'Tags', 'LastUpdate'];

}
