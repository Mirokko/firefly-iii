<?php

/*
 * ListController.php
 * Copyright (c) 2022 james@firefly-iii.org
 *
 * This file is part of Firefly III (https://github.com/firefly-iii).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace FireflyIII\Api\V2\Controllers\Model\Budget;

use FireflyIII\Api\V2\Controllers\Controller;
use FireflyIII\Exceptions\FireflyException;
use FireflyIII\Repositories\Budget\BudgetRepositoryInterface;
use FireflyIII\Transformers\V2\BudgetTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class ListController
 */
class ListController extends Controller
{
    private BudgetRepositoryInterface $repository;

    public function __construct()
    {
        parent::__construct();
        $this->middleware(
            function ($request, $next) {
                $this->repository = app(BudgetRepositoryInterface::class);

                return $next($request);
            }
        );
    }

    /**
     * This endpoint is documented at:
     * https://api-docs.firefly-iii.org/?urls.primaryName=2.0.0%20(v2)#/budgets/listBudgets
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        echo 'this needs move to Administration';
        throw new FireflyException('Needs migration to IndexController');
        $collection = $this->repository->getActiveBudgets();
        $total      = $collection->count();
        $collection->slice($this->pageXSize * $this->parameters->get('page'), $this->pXageSize);

        $paginator   = new LengthAwarePaginator($collection, $total, $this->pagXeSize, $this->parameters->get('page'));
        $transformer = new BudgetTransformer();

        return response()
            ->api($this->jsonApiList('budgets', $paginator, $transformer))
            ->header('Content-Type', self::CONTENT_TYPE);
    }
}
