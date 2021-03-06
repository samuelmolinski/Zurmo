<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class for mass edit. Allows temporary storage
     * of skipped record information during a mass edit action that
     * failed because the current user does not have permission::DELETE
     * on the model.
     */
    class MassDeleteInsufficientPermissionSkipSavingUtil
    {
        const STORAGE_ID = 'MassDeleteSkipInformation';

        /**
         * Resets storage cache.
         */
        public static function clear($modelClassName)
        {
            Yii::app()->session[self::getCacheId($modelClassName)] = null;
        }

        /**
         * Gets all storage cache data.
         * @return array of model id/name pairings.
         */
        public static function getAll($modelClassName)
        {
            return Yii::app()->session[self::getCacheId($modelClassName)];
        }

        /**
         * Gets count of storage data model id/name pairings
         */
        public static function getCount($modelClassName)
        {
            return count(Yii::app()->session[self::getCacheId($modelClassName)]);
        }

        /**
         * Set skip data by model id and name.
         */
        public static function setByModelIdAndName($modelClassName, $modelId, $modelName)
        {
            //todo: re-arrange once we are using something other than php session. This is a work around
            //for not being able to directly store multi-dimensional arrays.
            //http://www.yiiframework.com/forum/index.php?/topic/4262-multi-dimensional-arrays-in-session/
            $session        = Yii::app()->session;
            $temp           = $session[self::getCacheId($modelClassName)];
            $temp[$modelId] = $modelName;
            $session[self::getCacheId($modelClassName)] = $temp;
        }

        public static function getSkipCountMessageContentByModelClassName($skipCount, $modelClassName)
        {
            if ($skipCount > 0)
            {
                return $skipCount . ' ' .
                    LabelUtil::getUncapitalizedModelLabelByCountAndModelClassName($skipCount, $modelClassName) .
                    ' ' . Zurmo::t('ZurmoModule', 'skipped because you do not have sufficient permissions.');
            }
            throw new NotSupportedException();
        }

        /**
         * Resolve the successful count by taking the skip count into consideration. If the total count
         * was 10, but 6 were skipped, then the successful count returned is 4.
         * @return integer - successful count.
         */
        public static function resolveSuccessfulCountAgainstSkipCount($totalCount, $skipCount)
        {
            assert('$totalCount == 0  || ($totalCount > 0 && is_int($totalCount))');
            assert('$skipCount  == 0  || ($skipCount  > 0 && is_int($skipCount))');
            return ($totalCount - $skipCount);
        }

        protected static function getCacheId($modelClassName)
        {
            return $modelClassName . MassDeleteInsufficientPermissionSkipSavingUtil::STORAGE_ID;
        }
    }
?>