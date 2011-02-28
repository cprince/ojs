{**
 * synergiesoptin.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Counter plugin, Synergies opt in
 *}
{strip}
{assign var="pageTitle" value="plugins.generic.counter"}
{include file="common/header.tpl"}
{/strip}

{if $errors eq ''}
<p>Synergies Opt In Recorded</p>
{else}
<p>{$errors}</p>
{/if}


{include file="common/footer.tpl"}
