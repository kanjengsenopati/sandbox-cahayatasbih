import { r as reactExports, f as functionalUpdate$1, a as arraysEqual, c as createLRUCache, i as isPromise, b as isRedirect, d as isNotFound, e as invariant, g as createControlledPromise, h as rootRouteId, j as isServer$1, k as compileDecodeCharMap, t as trimPath, l as rewriteBasepath, m as composeRewrites, p as processRouteTree, n as processRouteMasks, o as resolvePath, q as cleanPath, s as trimPathRight, u as parseHref, v as executeRewriteInput, w as isDangerousProtocol, x as redirect, y as findSingleMatch, z as deepEqual, D as DEFAULT_PROTOCOL_ALLOWLIST, A as interpolatePath, B as nullReplaceEqualDeep, C as replaceEqualDeep$1, E as last, F as decodePath, G as findFlatMatch, H as findRouteMatch, I as executeRewriteOutput, J as encodePathLikeUrl, K as trimPathLeft, L as joinPaths, M as useRouter, N as dummyMatchContext, O as matchContext, P as requireReactDom, Q as exactPathTest, R as removeTrailingSlash, S as React, T as jsxRuntimeExports, U as isModuleNotFoundError, V as useHydrated, W as escapeHtml, X as isInlinableStylesheet, Y as getAssetCrossOrigin, Z as resolveManifestAssetLink, _ as getDefaultExportFromCjs, $ as getAugmentedNamespace, a0 as Outlet } from "./server-B6wkPDkD.js";
import require$$1 from "util";
import stream, { Readable } from "stream";
import require$$1$1, { resolve } from "path";
import require$$3 from "http";
import require$$4 from "https";
import require$$5 from "url";
import require$$6 from "fs";
import require$$8 from "crypto";
import http2 from "http2";
import require$$4$1 from "assert";
import { W as WriteStream, R as ReadStream } from "./worker-entry-zXm__Ov7.js";
import require$$0$2 from "os";
import zlib from "zlib";
import { EventEmitter } from "events";
const isatty = function() {
  return false;
};
const tty = {
  ReadStream,
  WriteStream,
  isatty
};
const tty$1 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  ReadStream,
  WriteStream,
  default: tty,
  isatty
}, Symbol.toStringTag, { value: "Module" }));
var reactUse = reactExports.use;
function useForwardedRef(ref2) {
  const innerRef = reactExports.useRef(null);
  reactExports.useImperativeHandle(ref2, () => innerRef.current, []);
  return innerRef;
}
function encode$2(obj, stringify = String) {
  const result = new URLSearchParams();
  for (const key in obj) {
    const val = obj[key];
    if (val !== void 0) result.set(key, stringify(val));
  }
  return result.toString();
}
function toValue(str) {
  if (!str) return "";
  if (str === "false") return false;
  if (str === "true") return true;
  return +str * 0 === 0 && +str + "" === str ? +str : str;
}
function decode(str) {
  const searchParams = new URLSearchParams(str);
  const result = /* @__PURE__ */ Object.create(null);
  for (const [key, value] of searchParams.entries()) {
    const previousValue = result[key];
    if (previousValue == null) result[key] = toValue(value);
    else if (Array.isArray(previousValue)) previousValue.push(toValue(value));
    else result[key] = [previousValue, toValue(value)];
  }
  return result;
}
var defaultParseSearch = parseSearchWith(JSON.parse);
var defaultStringifySearch = stringifySearchWith(JSON.stringify, JSON.parse);
function parseSearchWith(parser) {
  return (searchStr) => {
    if (searchStr[0] === "?") searchStr = searchStr.substring(1);
    const query = decode(searchStr);
    for (const key in query) {
      const value = query[key];
      if (typeof value === "string") try {
        query[key] = parser(value);
      } catch (_err) {
      }
    }
    return query;
  };
}
function stringifySearchWith(stringify, parser) {
  const hasParser = typeof parser === "function";
  function stringifyValue(val) {
    if (typeof val === "object" && val !== null) try {
      return stringify(val);
    } catch (_err) {
    }
    else if (hasParser && typeof val === "string") try {
      parser(val);
      return stringify(val);
    } catch (_err) {
    }
    return val;
  }
  return (search) => {
    const searchStr = encode$2(search, stringifyValue);
    return searchStr ? `?${searchStr}` : "";
  };
}
function createNonReactiveMutableStore(initialValue) {
  let value = initialValue;
  return {
    get() {
      return value;
    },
    set(nextOrUpdater) {
      value = functionalUpdate$1(nextOrUpdater, value);
    }
  };
}
function createNonReactiveReadonlyStore(read) {
  return { get() {
    return read();
  } };
}
function createRouterStores(initialState, config) {
  const { createMutableStore, createReadonlyStore, batch, init } = config;
  const matchStores = /* @__PURE__ */ new Map();
  const pendingMatchStores = /* @__PURE__ */ new Map();
  const cachedMatchStores = /* @__PURE__ */ new Map();
  const status = createMutableStore(initialState.status);
  const loadedAt = createMutableStore(initialState.loadedAt);
  const isLoading = createMutableStore(initialState.isLoading);
  const isTransitioning = createMutableStore(initialState.isTransitioning);
  const location = createMutableStore(initialState.location);
  const resolvedLocation = createMutableStore(initialState.resolvedLocation);
  const statusCode = createMutableStore(initialState.statusCode);
  const redirect2 = createMutableStore(initialState.redirect);
  const matchesId = createMutableStore([]);
  const pendingIds = createMutableStore([]);
  const cachedIds = createMutableStore([]);
  const matches = createReadonlyStore(() => readPoolMatches(matchStores, matchesId.get()));
  const pendingMatches = createReadonlyStore(() => readPoolMatches(pendingMatchStores, pendingIds.get()));
  const cachedMatches = createReadonlyStore(() => readPoolMatches(cachedMatchStores, cachedIds.get()));
  const firstId = createReadonlyStore(() => matchesId.get()[0]);
  const hasPending = createReadonlyStore(() => matchesId.get().some((matchId) => {
    return matchStores.get(matchId)?.get().status === "pending";
  }));
  const matchRouteDeps = createReadonlyStore(() => ({
    locationHref: location.get().href,
    resolvedLocationHref: resolvedLocation.get()?.href,
    status: status.get()
  }));
  const __store = createReadonlyStore(() => ({
    status: status.get(),
    loadedAt: loadedAt.get(),
    isLoading: isLoading.get(),
    isTransitioning: isTransitioning.get(),
    matches: matches.get(),
    location: location.get(),
    resolvedLocation: resolvedLocation.get(),
    statusCode: statusCode.get(),
    redirect: redirect2.get()
  }));
  const matchStoreByRouteIdCache = createLRUCache(64);
  function getRouteMatchStore(routeId) {
    let cached = matchStoreByRouteIdCache.get(routeId);
    if (!cached) {
      cached = createReadonlyStore(() => {
        const ids = matchesId.get();
        for (const id of ids) {
          const matchStore = matchStores.get(id);
          if (matchStore && matchStore.routeId === routeId) return matchStore.get();
        }
      });
      matchStoreByRouteIdCache.set(routeId, cached);
    }
    return cached;
  }
  const store = {
    status,
    loadedAt,
    isLoading,
    isTransitioning,
    location,
    resolvedLocation,
    statusCode,
    redirect: redirect2,
    matchesId,
    pendingIds,
    cachedIds,
    matches,
    pendingMatches,
    cachedMatches,
    firstId,
    hasPending,
    matchRouteDeps,
    matchStores,
    pendingMatchStores,
    cachedMatchStores,
    __store,
    getRouteMatchStore,
    setMatches,
    setPending,
    setCached
  };
  setMatches(initialState.matches);
  init?.(store);
  function setMatches(nextMatches) {
    reconcileMatchPool(nextMatches, matchStores, matchesId, createMutableStore, batch);
  }
  function setPending(nextMatches) {
    reconcileMatchPool(nextMatches, pendingMatchStores, pendingIds, createMutableStore, batch);
  }
  function setCached(nextMatches) {
    reconcileMatchPool(nextMatches, cachedMatchStores, cachedIds, createMutableStore, batch);
  }
  return store;
}
function readPoolMatches(pool, ids) {
  const matches = [];
  for (const id of ids) {
    const matchStore = pool.get(id);
    if (matchStore) matches.push(matchStore.get());
  }
  return matches;
}
function reconcileMatchPool(nextMatches, pool, idStore, createMutableStore, batch) {
  const nextIds = nextMatches.map((d) => d.id);
  const nextIdSet = new Set(nextIds);
  batch(() => {
    for (const id of pool.keys()) if (!nextIdSet.has(id)) pool.delete(id);
    for (const nextMatch of nextMatches) {
      const existing = pool.get(nextMatch.id);
      if (!existing) {
        const matchStore = createMutableStore(nextMatch);
        matchStore.routeId = nextMatch.routeId;
        pool.set(nextMatch.id, matchStore);
        continue;
      }
      existing.routeId = nextMatch.routeId;
      if (existing.get() !== nextMatch) existing.set(nextMatch);
    }
    if (!arraysEqual(idStore.get(), nextIds)) idStore.set(nextIds);
  });
}
var triggerOnReady = (inner) => {
  if (!inner.rendered) {
    inner.rendered = true;
    return inner.onReady?.();
  }
};
var resolvePreload = (inner, matchId) => {
  return !!(inner.preload && !inner.router.stores.matchStores.has(matchId));
};
var buildMatchContext = (inner, index, includeCurrentMatch = true) => {
  const context = { ...inner.router.options.context ?? {} };
  const end = includeCurrentMatch ? index : index - 1;
  for (let i = 0; i <= end; i++) {
    const innerMatch = inner.matches[i];
    if (!innerMatch) continue;
    const m = inner.router.getMatch(innerMatch.id);
    if (!m) continue;
    Object.assign(context, m.__routeContext, m.__beforeLoadContext);
  }
  return context;
};
var getNotFoundBoundaryIndex = (inner, err) => {
  if (!inner.matches.length) return;
  const requestedRouteId = err.routeId;
  const matchedRootIndex = inner.matches.findIndex((m) => m.routeId === inner.router.routeTree.id);
  const rootIndex = matchedRootIndex >= 0 ? matchedRootIndex : 0;
  let startIndex = requestedRouteId ? inner.matches.findIndex((match) => match.routeId === requestedRouteId) : inner.firstBadMatchIndex ?? inner.matches.length - 1;
  if (startIndex < 0) startIndex = rootIndex;
  for (let i = startIndex; i >= 0; i--) {
    const match = inner.matches[i];
    if (inner.router.looseRoutesById[match.routeId].options.notFoundComponent) return i;
  }
  return requestedRouteId ? startIndex : rootIndex;
};
var handleRedirectAndNotFound = (inner, match, err) => {
  if (!isRedirect(err) && !isNotFound(err)) return;
  if (isRedirect(err) && err.redirectHandled && !err.options.reloadDocument) throw err;
  if (match) {
    match._nonReactive.beforeLoadPromise?.resolve();
    match._nonReactive.loaderPromise?.resolve();
    match._nonReactive.beforeLoadPromise = void 0;
    match._nonReactive.loaderPromise = void 0;
    match._nonReactive.error = err;
    inner.updateMatch(match.id, (prev) => ({
      ...prev,
      status: isRedirect(err) ? "redirected" : isNotFound(err) ? "notFound" : prev.status === "pending" ? "success" : prev.status,
      context: buildMatchContext(inner, match.index),
      isFetching: false,
      error: err
    }));
    if (isNotFound(err) && !err.routeId) err.routeId = match.routeId;
    match._nonReactive.loadPromise?.resolve();
  }
  if (isRedirect(err)) {
    inner.rendered = true;
    err.options._fromLocation = inner.location;
    err.redirectHandled = true;
    err = inner.router.resolveRedirect(err);
  }
  throw err;
};
var shouldSkipLoader = (inner, matchId) => {
  const match = inner.router.getMatch(matchId);
  if (!match) return true;
  if (match.ssr === false) return true;
  return false;
};
var syncMatchContext = (inner, matchId, index) => {
  const nextContext = buildMatchContext(inner, index);
  inner.updateMatch(matchId, (prev) => {
    return {
      ...prev,
      context: nextContext
    };
  });
};
var handleSerialError = (inner, index, err, routerCode) => {
  const { id: matchId, routeId } = inner.matches[index];
  const route = inner.router.looseRoutesById[routeId];
  if (err instanceof Promise) throw err;
  err.routerCode = routerCode;
  inner.firstBadMatchIndex ??= index;
  handleRedirectAndNotFound(inner, inner.router.getMatch(matchId), err);
  try {
    route.options.onError?.(err);
  } catch (errorHandlerErr) {
    err = errorHandlerErr;
    handleRedirectAndNotFound(inner, inner.router.getMatch(matchId), err);
  }
  inner.updateMatch(matchId, (prev) => {
    prev._nonReactive.beforeLoadPromise?.resolve();
    prev._nonReactive.beforeLoadPromise = void 0;
    prev._nonReactive.loadPromise?.resolve();
    return {
      ...prev,
      error: err,
      status: "error",
      isFetching: false,
      updatedAt: Date.now(),
      abortController: new AbortController()
    };
  });
  if (!inner.preload && !isRedirect(err) && !isNotFound(err)) inner.serialError ??= err;
};
var isBeforeLoadSsr = (inner, matchId, index, route) => {
  const existingMatch = inner.router.getMatch(matchId);
  const parentMatchId = inner.matches[index - 1]?.id;
  const parentMatch = parentMatchId ? inner.router.getMatch(parentMatchId) : void 0;
  if (inner.router.isShell()) {
    existingMatch.ssr = route.id === rootRouteId;
    return;
  }
  if (parentMatch?.ssr === false) {
    existingMatch.ssr = false;
    return;
  }
  const parentOverride = (tempSsr2) => {
    if (tempSsr2 === true && parentMatch?.ssr === "data-only") return "data-only";
    return tempSsr2;
  };
  const defaultSsr = inner.router.options.defaultSsr ?? true;
  if (route.options.ssr === void 0) {
    existingMatch.ssr = parentOverride(defaultSsr);
    return;
  }
  if (typeof route.options.ssr !== "function") {
    existingMatch.ssr = parentOverride(route.options.ssr);
    return;
  }
  const { search, params } = existingMatch;
  const ssrFnContext = {
    search: makeMaybe(search, existingMatch.searchError),
    params: makeMaybe(params, existingMatch.paramsError),
    location: inner.location,
    matches: inner.matches.map((match) => ({
      index: match.index,
      pathname: match.pathname,
      fullPath: match.fullPath,
      staticData: match.staticData,
      id: match.id,
      routeId: match.routeId,
      search: makeMaybe(match.search, match.searchError),
      params: makeMaybe(match.params, match.paramsError),
      ssr: match.ssr
    }))
  };
  const tempSsr = route.options.ssr(ssrFnContext);
  if (isPromise(tempSsr)) return tempSsr.then((ssr) => {
    existingMatch.ssr = parentOverride(ssr ?? defaultSsr);
  });
  existingMatch.ssr = parentOverride(tempSsr ?? defaultSsr);
};
var setupPendingTimeout = (inner, matchId, route, match) => {
  if (match._nonReactive.pendingTimeout !== void 0) return;
  const pendingMs = route.options.pendingMs ?? inner.router.options.defaultPendingMs;
  if (!!(inner.onReady && false)) {
    const pendingTimeout = setTimeout(() => {
      triggerOnReady(inner);
    }, pendingMs);
    match._nonReactive.pendingTimeout = pendingTimeout;
  }
};
var preBeforeLoadSetup = (inner, matchId, route) => {
  const existingMatch = inner.router.getMatch(matchId);
  if (!existingMatch._nonReactive.beforeLoadPromise && !existingMatch._nonReactive.loaderPromise) return;
  setupPendingTimeout(inner, matchId, route, existingMatch);
  const then = () => {
    const match = inner.router.getMatch(matchId);
    if (match.preload && (match.status === "redirected" || match.status === "notFound")) handleRedirectAndNotFound(inner, match, match.error);
  };
  return existingMatch._nonReactive.beforeLoadPromise ? existingMatch._nonReactive.beforeLoadPromise.then(then) : then();
};
var executeBeforeLoad = (inner, matchId, index, route) => {
  const match = inner.router.getMatch(matchId);
  let prevLoadPromise = match._nonReactive.loadPromise;
  match._nonReactive.loadPromise = createControlledPromise(() => {
    prevLoadPromise?.resolve();
    prevLoadPromise = void 0;
  });
  const { paramsError, searchError } = match;
  if (paramsError) handleSerialError(inner, index, paramsError, "PARSE_PARAMS");
  if (searchError) handleSerialError(inner, index, searchError, "VALIDATE_SEARCH");
  setupPendingTimeout(inner, matchId, route, match);
  const abortController = new AbortController();
  let isPending = false;
  const pending = () => {
    if (isPending) return;
    isPending = true;
    inner.updateMatch(matchId, (prev) => ({
      ...prev,
      isFetching: "beforeLoad",
      fetchCount: prev.fetchCount + 1,
      abortController
    }));
  };
  const resolve2 = () => {
    match._nonReactive.beforeLoadPromise?.resolve();
    match._nonReactive.beforeLoadPromise = void 0;
    inner.updateMatch(matchId, (prev) => ({
      ...prev,
      isFetching: false
    }));
  };
  if (!route.options.beforeLoad) {
    inner.router.batch(() => {
      pending();
      resolve2();
    });
    return;
  }
  match._nonReactive.beforeLoadPromise = createControlledPromise();
  const context = {
    ...buildMatchContext(inner, index, false),
    ...match.__routeContext
  };
  const { search, params, cause } = match;
  const preload = resolvePreload(inner, matchId);
  const beforeLoadFnContext = {
    search,
    abortController,
    params,
    preload,
    context,
    location: inner.location,
    navigate: (opts) => inner.router.navigate({
      ...opts,
      _fromLocation: inner.location
    }),
    buildLocation: inner.router.buildLocation,
    cause: preload ? "preload" : cause,
    matches: inner.matches,
    routeId: route.id,
    ...inner.router.options.additionalContext
  };
  const updateContext = (beforeLoadContext2) => {
    if (beforeLoadContext2 === void 0) {
      inner.router.batch(() => {
        pending();
        resolve2();
      });
      return;
    }
    if (isRedirect(beforeLoadContext2) || isNotFound(beforeLoadContext2)) {
      pending();
      handleSerialError(inner, index, beforeLoadContext2, "BEFORE_LOAD");
    }
    inner.router.batch(() => {
      pending();
      inner.updateMatch(matchId, (prev) => ({
        ...prev,
        __beforeLoadContext: beforeLoadContext2
      }));
      resolve2();
    });
  };
  let beforeLoadContext;
  try {
    beforeLoadContext = route.options.beforeLoad(beforeLoadFnContext);
    if (isPromise(beforeLoadContext)) {
      pending();
      return beforeLoadContext.catch((err) => {
        handleSerialError(inner, index, err, "BEFORE_LOAD");
      }).then(updateContext);
    }
  } catch (err) {
    pending();
    handleSerialError(inner, index, err, "BEFORE_LOAD");
  }
  updateContext(beforeLoadContext);
};
var handleBeforeLoad = (inner, index) => {
  const { id: matchId, routeId } = inner.matches[index];
  const route = inner.router.looseRoutesById[routeId];
  const serverSsr = () => {
    {
      const maybePromise = isBeforeLoadSsr(inner, matchId, index, route);
      if (isPromise(maybePromise)) return maybePromise.then(queueExecution);
    }
    return queueExecution();
  };
  const execute = () => executeBeforeLoad(inner, matchId, index, route);
  const queueExecution = () => {
    if (shouldSkipLoader(inner, matchId)) return;
    const result = preBeforeLoadSetup(inner, matchId, route);
    return isPromise(result) ? result.then(execute) : execute();
  };
  return serverSsr();
};
var executeHead = (inner, matchId, route) => {
  const match = inner.router.getMatch(matchId);
  if (!match) return;
  if (!route.options.head && !route.options.scripts && !route.options.headers) return;
  const assetContext = {
    ssr: inner.router.options.ssr,
    matches: inner.matches,
    match,
    params: match.params,
    loaderData: match.loaderData
  };
  return Promise.all([
    route.options.head?.(assetContext),
    route.options.scripts?.(assetContext),
    route.options.headers?.(assetContext)
  ]).then(([headFnContent, scripts, headers]) => {
    return {
      meta: headFnContent?.meta,
      links: headFnContent?.links,
      headScripts: headFnContent?.scripts,
      headers,
      scripts,
      styles: headFnContent?.styles
    };
  });
};
var getLoaderContext = (inner, matchPromises, matchId, index, route) => {
  const parentMatchPromise = matchPromises[index - 1];
  const { params, loaderDeps, abortController, cause } = inner.router.getMatch(matchId);
  const context = buildMatchContext(inner, index);
  const preload = resolvePreload(inner, matchId);
  return {
    params,
    deps: loaderDeps,
    preload: !!preload,
    parentMatchPromise,
    abortController,
    context,
    location: inner.location,
    navigate: (opts) => inner.router.navigate({
      ...opts,
      _fromLocation: inner.location
    }),
    cause: preload ? "preload" : cause,
    route,
    ...inner.router.options.additionalContext
  };
};
var runLoader = async (inner, matchPromises, matchId, index, route) => {
  try {
    const match = inner.router.getMatch(matchId);
    try {
      if (!(isServer$1 ?? inner.router.isServer) || match.ssr === true) loadRouteChunk(route);
      const routeLoader = route.options.loader;
      const loader = typeof routeLoader === "function" ? routeLoader : routeLoader?.handler;
      const loaderResult = loader?.(getLoaderContext(inner, matchPromises, matchId, index, route));
      const loaderResultIsPromise = !!loader && isPromise(loaderResult);
      if (!!(loaderResultIsPromise || route._lazyPromise || route._componentsPromise || route.options.head || route.options.scripts || route.options.headers || match._nonReactive.minPendingPromise)) inner.updateMatch(matchId, (prev) => ({
        ...prev,
        isFetching: "loader"
      }));
      if (loader) {
        const loaderData = loaderResultIsPromise ? await loaderResult : loaderResult;
        handleRedirectAndNotFound(inner, inner.router.getMatch(matchId), loaderData);
        if (loaderData !== void 0) inner.updateMatch(matchId, (prev) => ({
          ...prev,
          loaderData
        }));
      }
      if (route._lazyPromise) await route._lazyPromise;
      const pendingPromise = match._nonReactive.minPendingPromise;
      if (pendingPromise) await pendingPromise;
      if (route._componentsPromise) await route._componentsPromise;
      inner.updateMatch(matchId, (prev) => ({
        ...prev,
        error: void 0,
        context: buildMatchContext(inner, index),
        status: "success",
        isFetching: false,
        updatedAt: Date.now()
      }));
    } catch (e) {
      let error = e;
      if (error?.name === "AbortError") {
        if (match.abortController.signal.aborted) {
          match._nonReactive.loaderPromise?.resolve();
          match._nonReactive.loaderPromise = void 0;
          return;
        }
        inner.updateMatch(matchId, (prev) => ({
          ...prev,
          status: prev.status === "pending" ? "success" : prev.status,
          isFetching: false,
          context: buildMatchContext(inner, index)
        }));
        return;
      }
      const pendingPromise = match._nonReactive.minPendingPromise;
      if (pendingPromise) await pendingPromise;
      if (isNotFound(e)) await route.options.notFoundComponent?.preload?.();
      handleRedirectAndNotFound(inner, inner.router.getMatch(matchId), e);
      try {
        route.options.onError?.(e);
      } catch (onErrorError) {
        error = onErrorError;
        handleRedirectAndNotFound(inner, inner.router.getMatch(matchId), onErrorError);
      }
      if (!isRedirect(error) && !isNotFound(error)) await loadRouteChunk(route, ["errorComponent"]);
      inner.updateMatch(matchId, (prev) => ({
        ...prev,
        error,
        context: buildMatchContext(inner, index),
        status: "error",
        isFetching: false
      }));
    }
  } catch (err) {
    const match = inner.router.getMatch(matchId);
    if (match) match._nonReactive.loaderPromise = void 0;
    handleRedirectAndNotFound(inner, match, err);
  }
};
var loadRouteMatch = async (inner, matchPromises, index) => {
  async function handleLoader(preload, prevMatch, previousRouteMatchId, match2, route2) {
    const age = Date.now() - prevMatch.updatedAt;
    const staleAge = preload ? route2.options.preloadStaleTime ?? inner.router.options.defaultPreloadStaleTime ?? 3e4 : route2.options.staleTime ?? inner.router.options.defaultStaleTime ?? 0;
    const shouldReloadOption = route2.options.shouldReload;
    const shouldReload = typeof shouldReloadOption === "function" ? shouldReloadOption(getLoaderContext(inner, matchPromises, matchId, index, route2)) : shouldReloadOption;
    const { status, invalid } = match2;
    const staleMatchShouldReload = age >= staleAge && (!!inner.forceStaleReload || match2.cause === "enter" || previousRouteMatchId !== void 0 && previousRouteMatchId !== match2.id);
    loaderShouldRunAsync = status === "success" && (invalid || (shouldReload ?? staleMatchShouldReload));
    if (preload && route2.options.preload === false) ;
    else if (loaderShouldRunAsync && !inner.sync && shouldReloadInBackground) {
      loaderIsRunningAsync = true;
      (async () => {
        try {
          await runLoader(inner, matchPromises, matchId, index, route2);
          const match3 = inner.router.getMatch(matchId);
          match3._nonReactive.loaderPromise?.resolve();
          match3._nonReactive.loadPromise?.resolve();
          match3._nonReactive.loaderPromise = void 0;
          match3._nonReactive.loadPromise = void 0;
        } catch (err) {
          if (isRedirect(err)) await inner.router.navigate(err.options);
        }
      })();
    } else if (status !== "success" || loaderShouldRunAsync) await runLoader(inner, matchPromises, matchId, index, route2);
    else syncMatchContext(inner, matchId, index);
  }
  const { id: matchId, routeId } = inner.matches[index];
  let loaderShouldRunAsync = false;
  let loaderIsRunningAsync = false;
  const route = inner.router.looseRoutesById[routeId];
  const routeLoader = route.options.loader;
  const shouldReloadInBackground = ((typeof routeLoader === "function" ? void 0 : routeLoader?.staleReloadMode) ?? inner.router.options.defaultStaleReloadMode) !== "blocking";
  if (shouldSkipLoader(inner, matchId)) {
    if (!inner.router.getMatch(matchId)) return inner.matches[index];
    syncMatchContext(inner, matchId, index);
    return inner.router.getMatch(matchId);
  } else {
    const prevMatch = inner.router.getMatch(matchId);
    const activeIdAtIndex = inner.router.stores.matchesId.get()[index];
    const previousRouteMatchId = (activeIdAtIndex && inner.router.stores.matchStores.get(activeIdAtIndex) || null)?.routeId === routeId ? activeIdAtIndex : inner.router.stores.matches.get().find((d) => d.routeId === routeId)?.id;
    const preload = resolvePreload(inner, matchId);
    if (prevMatch._nonReactive.loaderPromise) {
      if (prevMatch.status === "success" && !inner.sync && !prevMatch.preload && shouldReloadInBackground) return prevMatch;
      await prevMatch._nonReactive.loaderPromise;
      const match2 = inner.router.getMatch(matchId);
      const error = match2._nonReactive.error || match2.error;
      if (error) handleRedirectAndNotFound(inner, match2, error);
      if (match2.status === "pending") await handleLoader(preload, prevMatch, previousRouteMatchId, match2, route);
    } else {
      const nextPreload = preload && !inner.router.stores.matchStores.has(matchId);
      const match2 = inner.router.getMatch(matchId);
      match2._nonReactive.loaderPromise = createControlledPromise();
      if (nextPreload !== match2.preload) inner.updateMatch(matchId, (prev) => ({
        ...prev,
        preload: nextPreload
      }));
      await handleLoader(preload, prevMatch, previousRouteMatchId, match2, route);
    }
  }
  const match = inner.router.getMatch(matchId);
  if (!loaderIsRunningAsync) {
    match._nonReactive.loaderPromise?.resolve();
    match._nonReactive.loadPromise?.resolve();
    match._nonReactive.loadPromise = void 0;
  }
  clearTimeout(match._nonReactive.pendingTimeout);
  match._nonReactive.pendingTimeout = void 0;
  if (!loaderIsRunningAsync) match._nonReactive.loaderPromise = void 0;
  match._nonReactive.dehydrated = void 0;
  const nextIsFetching = loaderIsRunningAsync ? match.isFetching : false;
  if (nextIsFetching !== match.isFetching || match.invalid !== false) {
    inner.updateMatch(matchId, (prev) => ({
      ...prev,
      isFetching: nextIsFetching,
      invalid: false
    }));
    return inner.router.getMatch(matchId);
  } else return match;
};
async function loadMatches(arg) {
  const inner = arg;
  const matchPromises = [];
  let beforeLoadNotFound;
  for (let i = 0; i < inner.matches.length; i++) {
    try {
      const beforeLoad = handleBeforeLoad(inner, i);
      if (isPromise(beforeLoad)) await beforeLoad;
    } catch (err) {
      if (isRedirect(err)) throw err;
      if (isNotFound(err)) beforeLoadNotFound = err;
      else if (!inner.preload) throw err;
      break;
    }
    if (inner.serialError || inner.firstBadMatchIndex != null) break;
  }
  const baseMaxIndexExclusive = inner.firstBadMatchIndex ?? inner.matches.length;
  const boundaryIndex = beforeLoadNotFound && !inner.preload ? getNotFoundBoundaryIndex(inner, beforeLoadNotFound) : void 0;
  const maxIndexExclusive = beforeLoadNotFound && inner.preload ? 0 : boundaryIndex !== void 0 ? Math.min(boundaryIndex + 1, baseMaxIndexExclusive) : baseMaxIndexExclusive;
  let firstNotFound;
  let firstUnhandledRejection;
  for (let i = 0; i < maxIndexExclusive; i++) matchPromises.push(loadRouteMatch(inner, matchPromises, i));
  try {
    await Promise.all(matchPromises);
  } catch {
    const settled = await Promise.allSettled(matchPromises);
    for (const result of settled) {
      if (result.status !== "rejected") continue;
      const reason = result.reason;
      if (isRedirect(reason)) throw reason;
      if (isNotFound(reason)) firstNotFound ??= reason;
      else firstUnhandledRejection ??= reason;
    }
    if (firstUnhandledRejection !== void 0) throw firstUnhandledRejection;
  }
  const notFoundToThrow = firstNotFound ?? (beforeLoadNotFound && !inner.preload ? beforeLoadNotFound : void 0);
  let headMaxIndex = inner.firstBadMatchIndex !== void 0 ? inner.firstBadMatchIndex : inner.matches.length - 1;
  if (!notFoundToThrow && beforeLoadNotFound && inner.preload) return inner.matches;
  if (notFoundToThrow) {
    const renderedBoundaryIndex = getNotFoundBoundaryIndex(inner, notFoundToThrow);
    if (renderedBoundaryIndex === void 0) {
      invariant();
    }
    const boundaryMatch = inner.matches[renderedBoundaryIndex];
    const boundaryRoute = inner.router.looseRoutesById[boundaryMatch.routeId];
    const defaultNotFoundComponent = inner.router.options?.defaultNotFoundComponent;
    if (!boundaryRoute.options.notFoundComponent && defaultNotFoundComponent) boundaryRoute.options.notFoundComponent = defaultNotFoundComponent;
    notFoundToThrow.routeId = boundaryMatch.routeId;
    const boundaryIsRoot = boundaryMatch.routeId === inner.router.routeTree.id;
    inner.updateMatch(boundaryMatch.id, (prev) => ({
      ...prev,
      ...boundaryIsRoot ? {
        status: "success",
        globalNotFound: true,
        error: void 0
      } : {
        status: "notFound",
        error: notFoundToThrow
      },
      isFetching: false
    }));
    headMaxIndex = renderedBoundaryIndex;
    await loadRouteChunk(boundaryRoute, ["notFoundComponent"]);
  } else if (!inner.preload) {
    const rootMatch = inner.matches[0];
    if (!rootMatch.globalNotFound) {
      if (inner.router.getMatch(rootMatch.id)?.globalNotFound) inner.updateMatch(rootMatch.id, (prev) => ({
        ...prev,
        globalNotFound: false,
        error: void 0
      }));
    }
  }
  if (inner.serialError && inner.firstBadMatchIndex !== void 0) {
    const errorRoute = inner.router.looseRoutesById[inner.matches[inner.firstBadMatchIndex].routeId];
    await loadRouteChunk(errorRoute, ["errorComponent"]);
  }
  for (let i = 0; i <= headMaxIndex; i++) {
    const { id: matchId, routeId } = inner.matches[i];
    const route = inner.router.looseRoutesById[routeId];
    try {
      const headResult = executeHead(inner, matchId, route);
      if (headResult) {
        const head = await headResult;
        inner.updateMatch(matchId, (prev) => ({
          ...prev,
          ...head
        }));
      }
    } catch (err) {
      console.error(`Error executing head for route ${routeId}:`, err);
    }
  }
  const readyPromise = triggerOnReady(inner);
  if (isPromise(readyPromise)) await readyPromise;
  if (notFoundToThrow) throw notFoundToThrow;
  if (inner.serialError && !inner.preload && !inner.onReady) throw inner.serialError;
  return inner.matches;
}
function preloadRouteComponents(route, componentTypesToLoad) {
  const preloads = componentTypesToLoad.map((type2) => route.options[type2]?.preload?.()).filter(Boolean);
  if (preloads.length === 0) return void 0;
  return Promise.all(preloads);
}
function loadRouteChunk(route, componentTypesToLoad = componentTypes) {
  if (!route._lazyLoaded && route._lazyPromise === void 0) if (route.lazyFn) route._lazyPromise = route.lazyFn().then((lazyRoute) => {
    const { id: _id, ...options } = lazyRoute.options;
    Object.assign(route.options, options);
    route._lazyLoaded = true;
    route._lazyPromise = void 0;
  });
  else route._lazyLoaded = true;
  const runAfterLazy = () => route._componentsLoaded ? void 0 : componentTypesToLoad === componentTypes ? (() => {
    if (route._componentsPromise === void 0) {
      const componentsPromise = preloadRouteComponents(route, componentTypes);
      if (componentsPromise) route._componentsPromise = componentsPromise.then(() => {
        route._componentsLoaded = true;
        route._componentsPromise = void 0;
      });
      else route._componentsLoaded = true;
    }
    return route._componentsPromise;
  })() : preloadRouteComponents(route, componentTypesToLoad);
  return route._lazyPromise ? route._lazyPromise.then(runAfterLazy) : runAfterLazy();
}
function makeMaybe(value, error) {
  if (error) return {
    status: "error",
    error
  };
  return {
    status: "success",
    value
  };
}
function routeNeedsPreload(route) {
  for (const componentType of componentTypes) if (route.options[componentType]?.preload) return true;
  return false;
}
var componentTypes = [
  "component",
  "errorComponent",
  "pendingComponent",
  "notFoundComponent"
];
function getLocationChangeInfo(location, resolvedLocation) {
  const fromLocation = resolvedLocation;
  const toLocation = location;
  return {
    fromLocation,
    toLocation,
    pathChanged: fromLocation?.pathname !== toLocation.pathname,
    hrefChanged: fromLocation?.href !== toLocation.href,
    hashChanged: fromLocation?.hash !== toLocation.hash
  };
}
var RouterCore = class {
  /**
  * @deprecated Use the `createRouter` function instead
  */
  constructor(options, getStoreConfig) {
    this.tempLocationKey = `${Math.round(Math.random() * 1e7)}`;
    this.resetNextScroll = true;
    this.shouldViewTransition = void 0;
    this.isViewTransitionTypesSupported = void 0;
    this.subscribers = /* @__PURE__ */ new Set();
    this.isScrollRestoring = false;
    this.isScrollRestorationSetup = false;
    this.startTransition = (fn) => fn();
    this.update = (newOptions) => {
      const prevOptions = this.options;
      const prevBasepath = this.basepath ?? prevOptions?.basepath ?? "/";
      const basepathWasUnset = this.basepath === void 0;
      const prevRewriteOption = prevOptions?.rewrite;
      this.options = {
        ...prevOptions,
        ...newOptions
      };
      this.isServer = this.options.isServer ?? typeof document === "undefined";
      this.protocolAllowlist = new Set(this.options.protocolAllowlist);
      if (this.options.pathParamsAllowedCharacters) this.pathParamsDecoder = compileDecodeCharMap(this.options.pathParamsAllowedCharacters);
      if (!this.history || this.options.history && this.options.history !== this.history) if (!this.options.history) ;
      else this.history = this.options.history;
      this.origin = this.options.origin;
      if (!this.origin) this.origin = "http://localhost";
      if (this.history) this.updateLatestLocation();
      if (this.options.routeTree !== this.routeTree) {
        this.routeTree = this.options.routeTree;
        let processRouteTreeResult;
        if (globalThis.__TSR_CACHE__ && globalThis.__TSR_CACHE__.routeTree === this.routeTree) {
          const cached = globalThis.__TSR_CACHE__;
          this.resolvePathCache = cached.resolvePathCache;
          processRouteTreeResult = cached.processRouteTreeResult;
        } else {
          this.resolvePathCache = createLRUCache(1e3);
          processRouteTreeResult = this.buildRouteTree();
          if (globalThis.__TSR_CACHE__ === void 0) globalThis.__TSR_CACHE__ = {
            routeTree: this.routeTree,
            processRouteTreeResult,
            resolvePathCache: this.resolvePathCache
          };
        }
        this.setRoutes(processRouteTreeResult);
      }
      if (!this.stores && this.latestLocation) {
        const config = this.getStoreConfig(this);
        this.batch = config.batch;
        this.stores = createRouterStores(getInitialRouterState(this.latestLocation), config);
      }
      let needsLocationUpdate = false;
      const nextBasepath = this.options.basepath ?? "/";
      const nextRewriteOption = this.options.rewrite;
      if (basepathWasUnset || prevBasepath !== nextBasepath || prevRewriteOption !== nextRewriteOption) {
        this.basepath = nextBasepath;
        const rewrites = [];
        const trimmed = trimPath(nextBasepath);
        if (trimmed && trimmed !== "/") rewrites.push(rewriteBasepath({ basepath: nextBasepath }));
        if (nextRewriteOption) rewrites.push(nextRewriteOption);
        this.rewrite = rewrites.length === 0 ? void 0 : rewrites.length === 1 ? rewrites[0] : composeRewrites(rewrites);
        if (this.history) this.updateLatestLocation();
        needsLocationUpdate = true;
      }
      if (needsLocationUpdate && this.stores) this.stores.location.set(this.latestLocation);
      if (typeof window !== "undefined" && "CSS" in window && typeof window.CSS?.supports === "function") this.isViewTransitionTypesSupported = window.CSS.supports("selector(:active-view-transition-type(a)");
    };
    this.updateLatestLocation = () => {
      this.latestLocation = this.parseLocation(this.history.location, this.latestLocation);
    };
    this.buildRouteTree = () => {
      const result = processRouteTree(this.routeTree, this.options.caseSensitive, (route, i) => {
        route.init({ originalIndex: i });
      });
      if (this.options.routeMasks) processRouteMasks(this.options.routeMasks, result.processedTree);
      return result;
    };
    this.subscribe = (eventType, fn) => {
      const listener = {
        eventType,
        fn
      };
      this.subscribers.add(listener);
      return () => {
        this.subscribers.delete(listener);
      };
    };
    this.emit = (routerEvent) => {
      this.subscribers.forEach((listener) => {
        if (listener.eventType === routerEvent.type) listener.fn(routerEvent);
      });
    };
    this.parseLocation = (locationToParse, previousLocation) => {
      const parse = ({ pathname, search, hash, href, state }) => {
        if (!this.rewrite && !/[ \x00-\x1f\x7f\u0080-\uffff]/.test(pathname)) {
          const parsedSearch2 = this.options.parseSearch(search);
          const searchStr2 = this.options.stringifySearch(parsedSearch2);
          return {
            href: pathname + searchStr2 + hash,
            publicHref: pathname + searchStr2 + hash,
            pathname: decodePath(pathname).path,
            external: false,
            searchStr: searchStr2,
            search: nullReplaceEqualDeep(previousLocation?.search, parsedSearch2),
            hash: decodePath(hash.slice(1)).path,
            state: replaceEqualDeep$1(previousLocation?.state, state)
          };
        }
        const fullUrl = new URL(href, this.origin);
        const url = executeRewriteInput(this.rewrite, fullUrl);
        const parsedSearch = this.options.parseSearch(url.search);
        const searchStr = this.options.stringifySearch(parsedSearch);
        url.search = searchStr;
        return {
          href: url.href.replace(url.origin, ""),
          publicHref: href,
          pathname: decodePath(url.pathname).path,
          external: !!this.rewrite && url.origin !== this.origin,
          searchStr,
          search: nullReplaceEqualDeep(previousLocation?.search, parsedSearch),
          hash: decodePath(url.hash.slice(1)).path,
          state: replaceEqualDeep$1(previousLocation?.state, state)
        };
      };
      const location = parse(locationToParse);
      const { __tempLocation, __tempKey } = location.state;
      if (__tempLocation && (!__tempKey || __tempKey === this.tempLocationKey)) {
        const parsedTempLocation = parse(__tempLocation);
        parsedTempLocation.state.key = location.state.key;
        parsedTempLocation.state.__TSR_key = location.state.__TSR_key;
        delete parsedTempLocation.state.__tempLocation;
        return {
          ...parsedTempLocation,
          maskedLocation: location
        };
      }
      return location;
    };
    this.resolvePathWithBase = (from, path) => {
      return resolvePath({
        base: from,
        to: cleanPath(path),
        trailingSlash: this.options.trailingSlash,
        cache: this.resolvePathCache
      });
    };
    this.matchRoutes = (pathnameOrNext, locationSearchOrOpts, opts) => {
      if (typeof pathnameOrNext === "string") return this.matchRoutesInternal({
        pathname: pathnameOrNext,
        search: locationSearchOrOpts
      }, opts);
      return this.matchRoutesInternal(pathnameOrNext, locationSearchOrOpts);
    };
    this.getMatchedRoutes = (pathname) => {
      return getMatchedRoutes({
        pathname,
        routesById: this.routesById,
        processedTree: this.processedTree
      });
    };
    this.cancelMatch = (id) => {
      const match = this.getMatch(id);
      if (!match) return;
      match.abortController.abort();
      clearTimeout(match._nonReactive.pendingTimeout);
      match._nonReactive.pendingTimeout = void 0;
    };
    this.cancelMatches = () => {
      this.stores.pendingIds.get().forEach((matchId) => {
        this.cancelMatch(matchId);
      });
      this.stores.matchesId.get().forEach((matchId) => {
        if (this.stores.pendingMatchStores.has(matchId)) return;
        const match = this.stores.matchStores.get(matchId)?.get();
        if (!match) return;
        if (match.status === "pending" || match.isFetching === "loader") this.cancelMatch(matchId);
      });
    };
    this.buildLocation = (opts) => {
      const build = (dest = {}) => {
        const currentLocation = dest._fromLocation || this.pendingBuiltLocation || this.latestLocation;
        const lightweightResult = this.matchRoutesLightweight(currentLocation);
        if (dest.from && false) ;
        const defaultedFromPath = dest.unsafeRelative === "path" ? currentLocation.pathname : dest.from ?? lightweightResult.fullPath;
        const fromPath = this.resolvePathWithBase(defaultedFromPath, ".");
        const fromSearch = lightweightResult.search;
        const fromParams = Object.assign(/* @__PURE__ */ Object.create(null), lightweightResult.params);
        const nextTo = dest.to ? this.resolvePathWithBase(fromPath, `${dest.to}`) : this.resolvePathWithBase(fromPath, ".");
        const nextParams = dest.params === false || dest.params === null ? /* @__PURE__ */ Object.create(null) : (dest.params ?? true) === true ? fromParams : Object.assign(fromParams, functionalUpdate$1(dest.params, fromParams));
        const destMatchResult = this.getMatchedRoutes(nextTo);
        let destRoutes = destMatchResult.matchedRoutes;
        if ((!destMatchResult.foundRoute || destMatchResult.foundRoute.path !== "/" && destMatchResult.routeParams["**"]) && this.options.notFoundRoute) destRoutes = [...destRoutes, this.options.notFoundRoute];
        if (Object.keys(nextParams).length > 0) for (const route of destRoutes) {
          const fn = route.options.params?.stringify ?? route.options.stringifyParams;
          if (fn) try {
            Object.assign(nextParams, fn(nextParams));
          } catch {
          }
        }
        const nextPathname = opts.leaveParams ? nextTo : decodePath(interpolatePath({
          path: nextTo,
          params: nextParams,
          decoder: this.pathParamsDecoder,
          server: this.isServer
        }).interpolatedPath).path;
        let nextSearch = fromSearch;
        if (opts._includeValidateSearch && this.options.search?.strict) {
          const validatedSearch = {};
          destRoutes.forEach((route) => {
            if (route.options.validateSearch) try {
              Object.assign(validatedSearch, validateSearch(route.options.validateSearch, {
                ...validatedSearch,
                ...nextSearch
              }));
            } catch {
            }
          });
          nextSearch = validatedSearch;
        }
        nextSearch = applySearchMiddleware({
          search: nextSearch,
          dest,
          destRoutes,
          _includeValidateSearch: opts._includeValidateSearch
        });
        nextSearch = nullReplaceEqualDeep(fromSearch, nextSearch);
        const searchStr = this.options.stringifySearch(nextSearch);
        const hash = dest.hash === true ? currentLocation.hash : dest.hash ? functionalUpdate$1(dest.hash, currentLocation.hash) : void 0;
        const hashStr = hash ? `#${hash}` : "";
        let nextState = dest.state === true ? currentLocation.state : dest.state ? functionalUpdate$1(dest.state, currentLocation.state) : {};
        nextState = replaceEqualDeep$1(currentLocation.state, nextState);
        const fullPath = `${nextPathname}${searchStr}${hashStr}`;
        let href;
        let publicHref;
        let external = false;
        if (this.rewrite) {
          const url = new URL(fullPath, this.origin);
          const rewrittenUrl = executeRewriteOutput(this.rewrite, url);
          href = url.href.replace(url.origin, "");
          if (rewrittenUrl.origin !== this.origin) {
            publicHref = rewrittenUrl.href;
            external = true;
          } else publicHref = rewrittenUrl.pathname + rewrittenUrl.search + rewrittenUrl.hash;
        } else {
          href = encodePathLikeUrl(fullPath);
          publicHref = href;
        }
        return {
          publicHref,
          href,
          pathname: nextPathname,
          search: nextSearch,
          searchStr,
          state: nextState,
          hash: hash ?? "",
          external,
          unmaskOnReload: dest.unmaskOnReload
        };
      };
      const buildWithMatches = (dest = {}, maskedDest) => {
        const next = build(dest);
        let maskedNext = maskedDest ? build(maskedDest) : void 0;
        if (!maskedNext) {
          const params = /* @__PURE__ */ Object.create(null);
          if (this.options.routeMasks) {
            const match = findFlatMatch(next.pathname, this.processedTree);
            if (match) {
              Object.assign(params, match.rawParams);
              const { from: _from, params: maskParams, ...maskProps } = match.route;
              const nextParams = maskParams === false || maskParams === null ? /* @__PURE__ */ Object.create(null) : (maskParams ?? true) === true ? params : Object.assign(params, functionalUpdate$1(maskParams, params));
              maskedDest = {
                from: opts.from,
                ...maskProps,
                params: nextParams
              };
              maskedNext = build(maskedDest);
            }
          }
        }
        if (maskedNext) next.maskedLocation = maskedNext;
        return next;
      };
      if (opts.mask) return buildWithMatches(opts, {
        from: opts.from,
        ...opts.mask
      });
      return buildWithMatches(opts);
    };
    this.commitLocation = async ({ viewTransition, ignoreBlocker, ...next }) => {
      const isSameState = () => {
        const ignoredProps = [
          "key",
          "__TSR_key",
          "__TSR_index",
          "__hashScrollIntoViewOptions"
        ];
        ignoredProps.forEach((prop) => {
          next.state[prop] = this.latestLocation.state[prop];
        });
        const isEqual = deepEqual(next.state, this.latestLocation.state);
        ignoredProps.forEach((prop) => {
          delete next.state[prop];
        });
        return isEqual;
      };
      const isSameUrl = trimPathRight(this.latestLocation.href) === trimPathRight(next.href);
      let previousCommitPromise = this.commitLocationPromise;
      this.commitLocationPromise = createControlledPromise(() => {
        previousCommitPromise?.resolve();
        previousCommitPromise = void 0;
      });
      if (isSameUrl && isSameState()) this.load();
      else {
        let { maskedLocation, hashScrollIntoView, ...nextHistory } = next;
        if (maskedLocation) {
          nextHistory = {
            ...maskedLocation,
            state: {
              ...maskedLocation.state,
              __tempKey: void 0,
              __tempLocation: {
                ...nextHistory,
                search: nextHistory.searchStr,
                state: {
                  ...nextHistory.state,
                  __tempKey: void 0,
                  __tempLocation: void 0,
                  __TSR_key: void 0,
                  key: void 0
                }
              }
            }
          };
          if (nextHistory.unmaskOnReload ?? this.options.unmaskOnReload ?? false) nextHistory.state.__tempKey = this.tempLocationKey;
        }
        nextHistory.state.__hashScrollIntoViewOptions = hashScrollIntoView ?? this.options.defaultHashScrollIntoView ?? true;
        this.shouldViewTransition = viewTransition;
        this.history[next.replace ? "replace" : "push"](nextHistory.publicHref, nextHistory.state, { ignoreBlocker });
      }
      this.resetNextScroll = next.resetScroll ?? true;
      if (!this.history.subscribers.size) this.load();
      return this.commitLocationPromise;
    };
    this.buildAndCommitLocation = ({ replace, resetScroll, hashScrollIntoView, viewTransition, ignoreBlocker, href, ...rest } = {}) => {
      if (href) {
        const currentIndex = this.history.location.state.__TSR_index;
        const parsed = parseHref(href, { __TSR_index: replace ? currentIndex : currentIndex + 1 });
        const hrefUrl = new URL(parsed.pathname, this.origin);
        rest.to = executeRewriteInput(this.rewrite, hrefUrl).pathname;
        rest.search = this.options.parseSearch(parsed.search);
        rest.hash = parsed.hash.slice(1);
      }
      const location = this.buildLocation({
        ...rest,
        _includeValidateSearch: true
      });
      this.pendingBuiltLocation = location;
      const commitPromise = this.commitLocation({
        ...location,
        viewTransition,
        replace,
        resetScroll,
        hashScrollIntoView,
        ignoreBlocker
      });
      Promise.resolve().then(() => {
        if (this.pendingBuiltLocation === location) this.pendingBuiltLocation = void 0;
      });
      return commitPromise;
    };
    this.navigate = async ({ to, reloadDocument, href, publicHref, ...rest }) => {
      let hrefIsUrl = false;
      if (href) try {
        new URL(`${href}`);
        hrefIsUrl = true;
      } catch {
      }
      if (hrefIsUrl && !reloadDocument) reloadDocument = true;
      if (reloadDocument) {
        if (to !== void 0 || !href) {
          const location = this.buildLocation({
            to,
            ...rest
          });
          href = href ?? location.publicHref;
          publicHref = publicHref ?? location.publicHref;
        }
        const reloadHref = !hrefIsUrl && publicHref ? publicHref : href;
        if (isDangerousProtocol(reloadHref, this.protocolAllowlist)) {
          return Promise.resolve();
        }
        if (!rest.ignoreBlocker) {
          const blockers = this.history.getBlockers?.() ?? [];
          for (const blocker of blockers) if (blocker?.blockerFn) {
            if (await blocker.blockerFn({
              currentLocation: this.latestLocation,
              nextLocation: this.latestLocation,
              action: "PUSH"
            })) return Promise.resolve();
          }
        }
        if (rest.replace) window.location.replace(reloadHref);
        else window.location.href = reloadHref;
        return Promise.resolve();
      }
      return this.buildAndCommitLocation({
        ...rest,
        href,
        to,
        _isNavigate: true
      });
    };
    this.beforeLoad = () => {
      this.cancelMatches();
      this.updateLatestLocation();
      {
        const nextLocation = this.buildLocation({
          to: this.latestLocation.pathname,
          search: true,
          params: true,
          hash: true,
          state: true,
          _includeValidateSearch: true
        });
        if (this.latestLocation.publicHref !== nextLocation.publicHref) {
          const href = this.getParsedLocationHref(nextLocation);
          if (nextLocation.external) throw redirect({ href });
          else throw redirect({
            href,
            _builtLocation: nextLocation
          });
        }
      }
      const pendingMatches = this.matchRoutes(this.latestLocation);
      const nextCachedMatches = this.stores.cachedMatches.get().filter((d) => !pendingMatches.some((e) => e.id === d.id));
      this.batch(() => {
        this.stores.status.set("pending");
        this.stores.statusCode.set(200);
        this.stores.isLoading.set(true);
        this.stores.location.set(this.latestLocation);
        this.stores.setPending(pendingMatches);
        this.stores.setCached(nextCachedMatches);
      });
    };
    this.load = async (opts) => {
      let redirect2;
      let notFound;
      let loadPromise;
      const previousLocation = this.stores.resolvedLocation.get() ?? this.stores.location.get();
      loadPromise = new Promise((resolve2) => {
        this.startTransition(async () => {
          try {
            this.beforeLoad();
            const next = this.latestLocation;
            const locationChangeInfo = getLocationChangeInfo(next, this.stores.resolvedLocation.get());
            if (!this.stores.redirect.get()) this.emit({
              type: "onBeforeNavigate",
              ...locationChangeInfo
            });
            this.emit({
              type: "onBeforeLoad",
              ...locationChangeInfo
            });
            await loadMatches({
              router: this,
              sync: opts?.sync,
              forceStaleReload: previousLocation.href === next.href,
              matches: this.stores.pendingMatches.get(),
              location: next,
              updateMatch: this.updateMatch,
              onReady: async () => {
                this.startTransition(() => {
                  this.startViewTransition(async () => {
                    let exitingMatches = null;
                    let hookExitingMatches = null;
                    let hookEnteringMatches = null;
                    let hookStayingMatches = null;
                    this.batch(() => {
                      const pendingMatches = this.stores.pendingMatches.get();
                      const mountPending = pendingMatches.length;
                      const currentMatches = this.stores.matches.get();
                      exitingMatches = mountPending ? currentMatches.filter((match) => !this.stores.pendingMatchStores.has(match.id)) : null;
                      const pendingRouteIds = /* @__PURE__ */ new Set();
                      for (const s of this.stores.pendingMatchStores.values()) if (s.routeId) pendingRouteIds.add(s.routeId);
                      const activeRouteIds = /* @__PURE__ */ new Set();
                      for (const s of this.stores.matchStores.values()) if (s.routeId) activeRouteIds.add(s.routeId);
                      hookExitingMatches = mountPending ? currentMatches.filter((match) => !pendingRouteIds.has(match.routeId)) : null;
                      hookEnteringMatches = mountPending ? pendingMatches.filter((match) => !activeRouteIds.has(match.routeId)) : null;
                      hookStayingMatches = mountPending ? pendingMatches.filter((match) => activeRouteIds.has(match.routeId)) : currentMatches;
                      this.stores.isLoading.set(false);
                      this.stores.loadedAt.set(Date.now());
                      if (mountPending) {
                        this.stores.setMatches(pendingMatches);
                        this.stores.setPending([]);
                        this.stores.setCached([...this.stores.cachedMatches.get(), ...exitingMatches.filter((d) => d.status !== "error" && d.status !== "notFound" && d.status !== "redirected")]);
                        this.clearExpiredCache();
                      }
                    });
                    for (const [matches, hook] of [
                      [hookExitingMatches, "onLeave"],
                      [hookEnteringMatches, "onEnter"],
                      [hookStayingMatches, "onStay"]
                    ]) {
                      if (!matches) continue;
                      for (const match of matches) this.looseRoutesById[match.routeId].options[hook]?.(match);
                    }
                  });
                });
              }
            });
          } catch (err) {
            if (isRedirect(err)) {
              redirect2 = err;
            } else if (isNotFound(err)) notFound = err;
            const nextStatusCode = redirect2 ? redirect2.status : notFound ? 404 : this.stores.matches.get().some((d) => d.status === "error") ? 500 : 200;
            this.batch(() => {
              this.stores.statusCode.set(nextStatusCode);
              this.stores.redirect.set(redirect2);
            });
          }
          if (this.latestLoadPromise === loadPromise) {
            this.commitLocationPromise?.resolve();
            this.latestLoadPromise = void 0;
            this.commitLocationPromise = void 0;
          }
          resolve2();
        });
      });
      this.latestLoadPromise = loadPromise;
      await loadPromise;
      while (this.latestLoadPromise && loadPromise !== this.latestLoadPromise) await this.latestLoadPromise;
      let newStatusCode = void 0;
      if (this.hasNotFoundMatch()) newStatusCode = 404;
      else if (this.stores.matches.get().some((d) => d.status === "error")) newStatusCode = 500;
      if (newStatusCode !== void 0) this.stores.statusCode.set(newStatusCode);
    };
    this.startViewTransition = (fn) => {
      const shouldViewTransition = this.shouldViewTransition ?? this.options.defaultViewTransition;
      this.shouldViewTransition = void 0;
      if (shouldViewTransition && typeof document !== "undefined" && "startViewTransition" in document && typeof document.startViewTransition === "function") {
        let startViewTransitionParams;
        if (typeof shouldViewTransition === "object" && this.isViewTransitionTypesSupported) {
          const next = this.latestLocation;
          const prevLocation = this.stores.resolvedLocation.get();
          const resolvedViewTransitionTypes = typeof shouldViewTransition.types === "function" ? shouldViewTransition.types(getLocationChangeInfo(next, prevLocation)) : shouldViewTransition.types;
          if (resolvedViewTransitionTypes === false) {
            fn();
            return;
          }
          startViewTransitionParams = {
            update: fn,
            types: resolvedViewTransitionTypes
          };
        } else startViewTransitionParams = fn;
        document.startViewTransition(startViewTransitionParams);
      } else fn();
    };
    this.updateMatch = (id, updater) => {
      this.startTransition(() => {
        const pendingMatch = this.stores.pendingMatchStores.get(id);
        if (pendingMatch) {
          pendingMatch.set(updater);
          return;
        }
        const activeMatch = this.stores.matchStores.get(id);
        if (activeMatch) {
          activeMatch.set(updater);
          return;
        }
        const cachedMatch = this.stores.cachedMatchStores.get(id);
        if (cachedMatch) {
          const next = updater(cachedMatch.get());
          if (next.status === "redirected") {
            if (this.stores.cachedMatchStores.delete(id)) this.stores.cachedIds.set((prev) => prev.filter((matchId) => matchId !== id));
          } else cachedMatch.set(next);
        }
      });
    };
    this.getMatch = (matchId) => {
      return this.stores.cachedMatchStores.get(matchId)?.get() ?? this.stores.pendingMatchStores.get(matchId)?.get() ?? this.stores.matchStores.get(matchId)?.get();
    };
    this.invalidate = (opts) => {
      const invalidate = (d) => {
        if (opts?.filter?.(d) ?? true) return {
          ...d,
          invalid: true,
          ...opts?.forcePending || d.status === "error" || d.status === "notFound" ? {
            status: "pending",
            error: void 0
          } : void 0
        };
        return d;
      };
      this.batch(() => {
        this.stores.setMatches(this.stores.matches.get().map(invalidate));
        this.stores.setCached(this.stores.cachedMatches.get().map(invalidate));
        this.stores.setPending(this.stores.pendingMatches.get().map(invalidate));
      });
      this.shouldViewTransition = false;
      return this.load({ sync: opts?.sync });
    };
    this.getParsedLocationHref = (location) => {
      return location.publicHref || "/";
    };
    this.resolveRedirect = (redirect2) => {
      const locationHeader = redirect2.headers.get("Location");
      if (!redirect2.options.href || redirect2.options._builtLocation) {
        const location = redirect2.options._builtLocation ?? this.buildLocation(redirect2.options);
        const href = this.getParsedLocationHref(location);
        redirect2.options.href = href;
        redirect2.headers.set("Location", href);
      } else if (locationHeader) try {
        const url = new URL(locationHeader);
        if (this.origin && url.origin === this.origin) {
          const href = url.pathname + url.search + url.hash;
          redirect2.options.href = href;
          redirect2.headers.set("Location", href);
        }
      } catch {
      }
      if (redirect2.options.href && !redirect2.options._builtLocation && isDangerousProtocol(redirect2.options.href, this.protocolAllowlist)) throw new Error("Redirect blocked: unsafe protocol");
      if (!redirect2.headers.get("Location")) redirect2.headers.set("Location", redirect2.options.href);
      return redirect2;
    };
    this.clearCache = (opts) => {
      const filter2 = opts?.filter;
      if (filter2 !== void 0) this.stores.setCached(this.stores.cachedMatches.get().filter((m) => !filter2(m)));
      else this.stores.setCached([]);
    };
    this.clearExpiredCache = () => {
      const now = Date.now();
      const filter2 = (d) => {
        const route = this.looseRoutesById[d.routeId];
        if (!route.options.loader) return true;
        const gcTime = (d.preload ? route.options.preloadGcTime ?? this.options.defaultPreloadGcTime : route.options.gcTime ?? this.options.defaultGcTime) ?? 300 * 1e3;
        if (d.status === "error") return true;
        return now - d.updatedAt >= gcTime;
      };
      this.clearCache({ filter: filter2 });
    };
    this.loadRouteChunk = loadRouteChunk;
    this.preloadRoute = async (opts) => {
      const next = opts._builtLocation ?? this.buildLocation(opts);
      let matches = this.matchRoutes(next, {
        throwOnError: true,
        preload: true,
        dest: opts
      });
      const activeMatchIds = /* @__PURE__ */ new Set([...this.stores.matchesId.get(), ...this.stores.pendingIds.get()]);
      const loadedMatchIds = /* @__PURE__ */ new Set([...activeMatchIds, ...this.stores.cachedIds.get()]);
      const matchesToCache = matches.filter((match) => !loadedMatchIds.has(match.id));
      if (matchesToCache.length) {
        const cachedMatches = this.stores.cachedMatches.get();
        this.stores.setCached([...cachedMatches, ...matchesToCache]);
      }
      try {
        matches = await loadMatches({
          router: this,
          matches,
          location: next,
          preload: true,
          updateMatch: (id, updater) => {
            if (activeMatchIds.has(id)) matches = matches.map((d) => d.id === id ? updater(d) : d);
            else this.updateMatch(id, updater);
          }
        });
        return matches;
      } catch (err) {
        if (isRedirect(err)) {
          if (err.options.reloadDocument) return;
          return await this.preloadRoute({
            ...err.options,
            _fromLocation: next
          });
        }
        if (!isNotFound(err)) console.error(err);
        return;
      }
    };
    this.matchRoute = (location, opts) => {
      const matchLocation = {
        ...location,
        to: location.to ? this.resolvePathWithBase(location.from || "", location.to) : void 0,
        params: location.params || {},
        leaveParams: true
      };
      const next = this.buildLocation(matchLocation);
      if (opts?.pending && this.stores.status.get() !== "pending") return false;
      const baseLocation = (opts?.pending === void 0 ? !this.stores.isLoading.get() : opts.pending) ? this.latestLocation : this.stores.resolvedLocation.get() || this.stores.location.get();
      const match = findSingleMatch(next.pathname, opts?.caseSensitive ?? false, opts?.fuzzy ?? false, baseLocation.pathname, this.processedTree);
      if (!match) return false;
      if (location.params) {
        if (!deepEqual(match.rawParams, location.params, { partial: true })) return false;
      }
      if (opts?.includeSearch ?? true) return deepEqual(baseLocation.search, next.search, { partial: true }) ? match.rawParams : false;
      return match.rawParams;
    };
    this.hasNotFoundMatch = () => {
      return this.stores.matches.get().some((d) => d.status === "notFound" || d.globalNotFound);
    };
    this.getStoreConfig = getStoreConfig;
    this.update({
      defaultPreloadDelay: 50,
      defaultPendingMs: 1e3,
      defaultPendingMinMs: 500,
      context: void 0,
      ...options,
      caseSensitive: options.caseSensitive ?? false,
      notFoundMode: options.notFoundMode ?? "fuzzy",
      stringifySearch: options.stringifySearch ?? defaultStringifySearch,
      parseSearch: options.parseSearch ?? defaultParseSearch,
      protocolAllowlist: options.protocolAllowlist ?? DEFAULT_PROTOCOL_ALLOWLIST
    });
    if (typeof document !== "undefined") self.__TSR_ROUTER__ = this;
  }
  isShell() {
    return !!this.options.isShell;
  }
  isPrerendering() {
    return !!this.options.isPrerendering;
  }
  get state() {
    return this.stores.__store.get();
  }
  setRoutes({ routesById, routesByPath, processedTree }) {
    this.routesById = routesById;
    this.routesByPath = routesByPath;
    this.processedTree = processedTree;
    const notFoundRoute = this.options.notFoundRoute;
    if (notFoundRoute) {
      notFoundRoute.init({ originalIndex: 99999999999 });
      this.routesById[notFoundRoute.id] = notFoundRoute;
    }
  }
  get looseRoutesById() {
    return this.routesById;
  }
  getParentContext(parentMatch) {
    return !parentMatch?.id ? this.options.context ?? void 0 : parentMatch.context ?? this.options.context ?? void 0;
  }
  matchRoutesInternal(next, opts) {
    const matchedRoutesResult = this.getMatchedRoutes(next.pathname);
    const { foundRoute, routeParams, parsedParams } = matchedRoutesResult;
    let { matchedRoutes } = matchedRoutesResult;
    let isGlobalNotFound = false;
    if (foundRoute ? foundRoute.path !== "/" && routeParams["**"] : trimPathRight(next.pathname)) if (this.options.notFoundRoute) matchedRoutes = [...matchedRoutes, this.options.notFoundRoute];
    else isGlobalNotFound = true;
    const globalNotFoundRouteId = isGlobalNotFound ? findGlobalNotFoundRouteId(this.options.notFoundMode, matchedRoutes) : void 0;
    const matches = new Array(matchedRoutes.length);
    const previousActiveMatchesByRouteId = /* @__PURE__ */ new Map();
    for (const store of this.stores.matchStores.values()) if (store.routeId) previousActiveMatchesByRouteId.set(store.routeId, store.get());
    for (let index = 0; index < matchedRoutes.length; index++) {
      const route = matchedRoutes[index];
      const parentMatch = matches[index - 1];
      let preMatchSearch;
      let strictMatchSearch;
      let searchError;
      {
        const parentSearch = parentMatch?.search ?? next.search;
        const parentStrictSearch = parentMatch?._strictSearch ?? void 0;
        try {
          const strictSearch = validateSearch(route.options.validateSearch, { ...parentSearch }) ?? void 0;
          preMatchSearch = {
            ...parentSearch,
            ...strictSearch
          };
          strictMatchSearch = {
            ...parentStrictSearch,
            ...strictSearch
          };
          searchError = void 0;
        } catch (err) {
          let searchParamError = err;
          if (!(err instanceof SearchParamError)) searchParamError = new SearchParamError(err.message, { cause: err });
          if (opts?.throwOnError) throw searchParamError;
          preMatchSearch = parentSearch;
          strictMatchSearch = {};
          searchError = searchParamError;
        }
      }
      const loaderDeps = route.options.loaderDeps?.({ search: preMatchSearch }) ?? "";
      const loaderDepsHash = loaderDeps ? JSON.stringify(loaderDeps) : "";
      const { interpolatedPath, usedParams } = interpolatePath({
        path: route.fullPath,
        params: routeParams,
        decoder: this.pathParamsDecoder,
        server: this.isServer
      });
      const matchId = route.id + interpolatedPath + loaderDepsHash;
      const existingMatch = this.getMatch(matchId);
      const previousMatch = previousActiveMatchesByRouteId.get(route.id);
      const strictParams = existingMatch?._strictParams ?? usedParams;
      let paramsError = void 0;
      if (!existingMatch) try {
        extractStrictParams(route, usedParams, parsedParams, strictParams);
      } catch (err) {
        if (isNotFound(err) || isRedirect(err)) paramsError = err;
        else paramsError = new PathParamError(err.message, { cause: err });
        if (opts?.throwOnError) throw paramsError;
      }
      Object.assign(routeParams, strictParams);
      const cause = previousMatch ? "stay" : "enter";
      let match;
      if (existingMatch) match = {
        ...existingMatch,
        cause,
        params: previousMatch?.params ?? routeParams,
        _strictParams: strictParams,
        search: previousMatch ? nullReplaceEqualDeep(previousMatch.search, preMatchSearch) : nullReplaceEqualDeep(existingMatch.search, preMatchSearch),
        _strictSearch: strictMatchSearch
      };
      else {
        const status = route.options.loader || route.options.beforeLoad || route.lazyFn || routeNeedsPreload(route) ? "pending" : "success";
        match = {
          id: matchId,
          ssr: void 0,
          index,
          routeId: route.id,
          params: previousMatch?.params ?? routeParams,
          _strictParams: strictParams,
          pathname: interpolatedPath,
          updatedAt: Date.now(),
          search: previousMatch ? nullReplaceEqualDeep(previousMatch.search, preMatchSearch) : preMatchSearch,
          _strictSearch: strictMatchSearch,
          searchError: void 0,
          status,
          isFetching: false,
          error: void 0,
          paramsError,
          __routeContext: void 0,
          _nonReactive: { loadPromise: createControlledPromise() },
          __beforeLoadContext: void 0,
          context: {},
          abortController: new AbortController(),
          fetchCount: 0,
          cause,
          loaderDeps: previousMatch ? replaceEqualDeep$1(previousMatch.loaderDeps, loaderDeps) : loaderDeps,
          invalid: false,
          preload: false,
          links: void 0,
          scripts: void 0,
          headScripts: void 0,
          meta: void 0,
          staticData: route.options.staticData || {},
          fullPath: route.fullPath
        };
      }
      if (!opts?.preload) match.globalNotFound = globalNotFoundRouteId === route.id;
      match.searchError = searchError;
      const parentContext = this.getParentContext(parentMatch);
      match.context = {
        ...parentContext,
        ...match.__routeContext,
        ...match.__beforeLoadContext
      };
      matches[index] = match;
    }
    for (let index = 0; index < matches.length; index++) {
      const match = matches[index];
      const route = this.looseRoutesById[match.routeId];
      const existingMatch = this.getMatch(match.id);
      const previousMatch = previousActiveMatchesByRouteId.get(match.routeId);
      match.params = previousMatch ? nullReplaceEqualDeep(previousMatch.params, routeParams) : routeParams;
      if (!existingMatch) {
        const parentMatch = matches[index - 1];
        const parentContext = this.getParentContext(parentMatch);
        if (route.options.context) {
          const contextFnContext = {
            deps: match.loaderDeps,
            params: match.params,
            context: parentContext ?? {},
            location: next,
            navigate: (opts2) => this.navigate({
              ...opts2,
              _fromLocation: next
            }),
            buildLocation: this.buildLocation,
            cause: match.cause,
            abortController: match.abortController,
            preload: !!match.preload,
            matches,
            routeId: route.id
          };
          match.__routeContext = route.options.context(contextFnContext) ?? void 0;
        }
        match.context = {
          ...parentContext,
          ...match.__routeContext,
          ...match.__beforeLoadContext
        };
      }
    }
    return matches;
  }
  /**
  * Lightweight route matching for buildLocation.
  * Only computes fullPath, accumulated search, and params - skipping expensive
  * operations like AbortController, ControlledPromise, loaderDeps, and full match objects.
  */
  matchRoutesLightweight(location) {
    const { matchedRoutes, routeParams, parsedParams } = this.getMatchedRoutes(location.pathname);
    const lastRoute = last(matchedRoutes);
    const accumulatedSearch = { ...location.search };
    for (const route of matchedRoutes) try {
      Object.assign(accumulatedSearch, validateSearch(route.options.validateSearch, accumulatedSearch));
    } catch {
    }
    const lastStateMatchId = last(this.stores.matchesId.get());
    const lastStateMatch = lastStateMatchId && this.stores.matchStores.get(lastStateMatchId)?.get();
    const canReuseParams = lastStateMatch && lastStateMatch.routeId === lastRoute.id && lastStateMatch.pathname === location.pathname;
    let params;
    if (canReuseParams) params = lastStateMatch.params;
    else {
      const strictParams = Object.assign(/* @__PURE__ */ Object.create(null), routeParams);
      for (const route of matchedRoutes) try {
        extractStrictParams(route, routeParams, parsedParams ?? {}, strictParams);
      } catch {
      }
      params = strictParams;
    }
    return {
      matchedRoutes,
      fullPath: lastRoute.fullPath,
      search: accumulatedSearch,
      params
    };
  }
};
var SearchParamError = class extends Error {
};
var PathParamError = class extends Error {
};
function getInitialRouterState(location) {
  return {
    loadedAt: 0,
    isLoading: false,
    isTransitioning: false,
    status: "idle",
    resolvedLocation: void 0,
    location,
    matches: [],
    statusCode: 200
  };
}
function validateSearch(validateSearch2, input) {
  if (validateSearch2 == null) return {};
  if ("~standard" in validateSearch2) {
    const result = validateSearch2["~standard"].validate(input);
    if (result instanceof Promise) throw new SearchParamError("Async validation not supported");
    if (result.issues) throw new SearchParamError(JSON.stringify(result.issues, void 0, 2), { cause: result });
    return result.value;
  }
  if ("parse" in validateSearch2) return validateSearch2.parse(input);
  if (typeof validateSearch2 === "function") return validateSearch2(input);
  return {};
}
function getMatchedRoutes({ pathname, routesById, processedTree }) {
  const routeParams = /* @__PURE__ */ Object.create(null);
  const trimmedPath = trimPathRight(pathname);
  let foundRoute = void 0;
  let parsedParams = void 0;
  const match = findRouteMatch(trimmedPath, processedTree, true);
  if (match) {
    foundRoute = match.route;
    Object.assign(routeParams, match.rawParams);
    parsedParams = Object.assign(/* @__PURE__ */ Object.create(null), match.parsedParams);
  }
  return {
    matchedRoutes: match?.branch || [routesById["__root__"]],
    routeParams,
    foundRoute,
    parsedParams
  };
}
function applySearchMiddleware({ search, dest, destRoutes, _includeValidateSearch }) {
  return buildMiddlewareChain(destRoutes)(search, dest, _includeValidateSearch ?? false);
}
function buildMiddlewareChain(destRoutes) {
  const context = {
    dest: null,
    _includeValidateSearch: false,
    middlewares: []
  };
  for (const route of destRoutes) {
    if ("search" in route.options) {
      if (route.options.search?.middlewares) context.middlewares.push(...route.options.search.middlewares);
    } else if (route.options.preSearchFilters || route.options.postSearchFilters) {
      const legacyMiddleware = ({ search, next }) => {
        let nextSearch = search;
        if ("preSearchFilters" in route.options && route.options.preSearchFilters) nextSearch = route.options.preSearchFilters.reduce((prev, next2) => next2(prev), search);
        const result = next(nextSearch);
        if ("postSearchFilters" in route.options && route.options.postSearchFilters) return route.options.postSearchFilters.reduce((prev, next2) => next2(prev), result);
        return result;
      };
      context.middlewares.push(legacyMiddleware);
    }
    if (route.options.validateSearch) {
      const validate = ({ search, next }) => {
        const result = next(search);
        if (!context._includeValidateSearch) return result;
        try {
          return {
            ...result,
            ...validateSearch(route.options.validateSearch, result) ?? void 0
          };
        } catch {
          return result;
        }
      };
      context.middlewares.push(validate);
    }
  }
  const final = ({ search }) => {
    const dest = context.dest;
    if (!dest.search) return {};
    if (dest.search === true) return search;
    return functionalUpdate$1(dest.search, search);
  };
  context.middlewares.push(final);
  const applyNext = (index, currentSearch, middlewares) => {
    if (index >= middlewares.length) return currentSearch;
    const middleware = middlewares[index];
    const next = (newSearch) => {
      return applyNext(index + 1, newSearch, middlewares);
    };
    return middleware({
      search: currentSearch,
      next
    });
  };
  return function middleware(search, dest, _includeValidateSearch) {
    context.dest = dest;
    context._includeValidateSearch = _includeValidateSearch;
    return applyNext(0, search, context.middlewares);
  };
}
function findGlobalNotFoundRouteId(notFoundMode, routes) {
  if (notFoundMode !== "root") for (let i = routes.length - 1; i >= 0; i--) {
    const route = routes[i];
    if (route.children) return route.id;
  }
  return rootRouteId;
}
function extractStrictParams(route, referenceParams, parsedParams, accumulatedParams) {
  const parseParams = route.options.params?.parse ?? route.options.parseParams;
  if (parseParams) if (route.options.skipRouteOnParseError) {
    for (const key in referenceParams) if (key in parsedParams) accumulatedParams[key] = parsedParams[key];
  } else {
    const result = parseParams(accumulatedParams);
    Object.assign(accumulatedParams, result);
  }
}
var BaseRoute = class {
  get to() {
    return this._to;
  }
  get id() {
    return this._id;
  }
  get path() {
    return this._path;
  }
  get fullPath() {
    return this._fullPath;
  }
  constructor(options) {
    this.init = (opts) => {
      this.originalIndex = opts.originalIndex;
      const options2 = this.options;
      const isRoot = !options2?.path && !options2?.id;
      this.parentRoute = this.options.getParentRoute?.();
      if (isRoot) this._path = rootRouteId;
      else if (!this.parentRoute) {
        invariant();
      }
      let path = isRoot ? rootRouteId : options2?.path;
      if (path && path !== "/") path = trimPathLeft(path);
      const customId = options2?.id || path;
      let id = isRoot ? rootRouteId : joinPaths([this.parentRoute.id === "__root__" ? "" : this.parentRoute.id, customId]);
      if (path === "__root__") path = "/";
      if (id !== "__root__") id = joinPaths(["/", id]);
      const fullPath = id === "__root__" ? "/" : joinPaths([this.parentRoute.fullPath, path]);
      this._path = path;
      this._id = id;
      this._fullPath = fullPath;
      this._to = trimPathRight(fullPath);
    };
    this.addChildren = (children) => {
      return this._addFileChildren(children);
    };
    this._addFileChildren = (children) => {
      if (Array.isArray(children)) this.children = children;
      if (typeof children === "object" && children !== null) this.children = Object.values(children);
      return this;
    };
    this._addFileTypes = () => {
      return this;
    };
    this.updateLoader = (options2) => {
      Object.assign(this.options, options2);
      return this;
    };
    this.update = (options2) => {
      Object.assign(this.options, options2);
      return this;
    };
    this.lazy = (lazyFn) => {
      this.lazyFn = lazyFn;
      return this;
    };
    this.redirect = (opts) => redirect({
      from: this.fullPath,
      ...opts
    });
    this.options = options || {};
    this.isRoot = !options?.getParentRoute;
    if (options?.id && options?.path) throw new Error(`Route cannot have both an 'id' and a 'path' option.`);
  }
};
var BaseRootRoute = class extends BaseRoute {
  constructor(options) {
    super(options);
  }
};
function useMatch(opts) {
  const router2 = useRouter();
  const nearestMatchId = reactExports.useContext(opts.from ? dummyMatchContext : matchContext);
  const key = opts.from ?? nearestMatchId;
  const matchStore = key ? opts.from ? router2.stores.getRouteMatchStore(key) : router2.stores.matchStores.get(key) : void 0;
  {
    const match = matchStore?.get();
    if ((opts.shouldThrow ?? true) && !match) {
      invariant();
    }
    if (match === void 0) return;
    return opts.select ? opts.select(match) : match;
  }
}
function useLoaderData(opts) {
  return useMatch({
    from: opts.from,
    strict: opts.strict,
    structuralSharing: opts.structuralSharing,
    select: (s) => {
      return opts.select ? opts.select(s.loaderData) : s.loaderData;
    }
  });
}
function useLoaderDeps(opts) {
  const { select, ...rest } = opts;
  return useMatch({
    ...rest,
    select: (s) => {
      return select ? select(s.loaderDeps) : s.loaderDeps;
    }
  });
}
function useParams(opts) {
  return useMatch({
    from: opts.from,
    shouldThrow: opts.shouldThrow,
    structuralSharing: opts.structuralSharing,
    strict: opts.strict,
    select: (match) => {
      const params = opts.strict === false ? match.params : match._strictParams;
      return opts.select ? opts.select(params) : params;
    }
  });
}
function useSearch(opts) {
  return useMatch({
    from: opts.from,
    strict: opts.strict,
    shouldThrow: opts.shouldThrow,
    structuralSharing: opts.structuralSharing,
    select: (match) => {
      return opts.select ? opts.select(match.search) : match.search;
    }
  });
}
function useNavigate(_defaultOpts) {
  const router2 = useRouter();
  return reactExports.useCallback((options) => {
    return router2.navigate({
      ...options,
      from: options.from ?? _defaultOpts?.from
    });
  }, [_defaultOpts?.from, router2]);
}
function useRouteContext(opts) {
  return useMatch({
    ...opts,
    select: (match) => opts.select ? opts.select(match.context) : match.context
  });
}
requireReactDom();
function useLinkProps(options, forwardedRef) {
  const router2 = useRouter();
  const innerRef = useForwardedRef(forwardedRef);
  const { activeProps, inactiveProps, activeOptions, to, preload: userPreload, preloadDelay: userPreloadDelay, preloadIntentProximity: _preloadIntentProximity, hashScrollIntoView, replace, startTransition, resetScroll, viewTransition, children, target, disabled, style, className, onClick, onBlur, onFocus, onMouseEnter, onMouseLeave, onTouchStart, ignoreBlocker, params: _params, search: _search, hash: _hash, state: _state, mask: _mask, reloadDocument: _reloadDocument, unsafeRelative: _unsafeRelative, from: _from, _fromLocation, ...propsSafeToSpread } = options;
  {
    const safeInternal = isSafeInternal(to);
    if (typeof to === "string" && !safeInternal && to.indexOf(":") > -1) try {
      new URL(to);
      if (isDangerousProtocol(to, router2.protocolAllowlist)) {
        if (false) ;
        return {
          ...propsSafeToSpread,
          ref: innerRef,
          href: void 0,
          ...children && { children },
          ...target && { target },
          ...disabled && { disabled },
          ...style && { style },
          ...className && { className }
        };
      }
      return {
        ...propsSafeToSpread,
        ref: innerRef,
        href: to,
        ...children && { children },
        ...target && { target },
        ...disabled && { disabled },
        ...style && { style },
        ...className && { className }
      };
    } catch {
    }
    const next2 = router2.buildLocation({
      ...options,
      from: options.from
    });
    const hrefOption2 = getHrefOption(next2.maskedLocation ? next2.maskedLocation.publicHref : next2.publicHref, next2.maskedLocation ? next2.maskedLocation.external : next2.external, router2.history, disabled);
    const externalLink2 = (() => {
      if (hrefOption2?.external) {
        if (isDangerousProtocol(hrefOption2.href, router2.protocolAllowlist)) {
          return;
        }
        return hrefOption2.href;
      }
      if (safeInternal) return void 0;
      if (typeof to === "string" && to.indexOf(":") > -1) try {
        new URL(to);
        if (isDangerousProtocol(to, router2.protocolAllowlist)) {
          if (false) ;
          return;
        }
        return to;
      } catch {
      }
    })();
    const isActive2 = (() => {
      if (externalLink2) return false;
      const currentLocation2 = router2.stores.location.get();
      const exact = activeOptions?.exact ?? false;
      if (exact) {
        if (!exactPathTest(currentLocation2.pathname, next2.pathname, router2.basepath)) return false;
      } else {
        const currentPathSplit = removeTrailingSlash(currentLocation2.pathname, router2.basepath);
        const nextPathSplit = removeTrailingSlash(next2.pathname, router2.basepath);
        if (!(currentPathSplit.startsWith(nextPathSplit) && (currentPathSplit.length === nextPathSplit.length || currentPathSplit[nextPathSplit.length] === "/"))) return false;
      }
      if (activeOptions?.includeSearch ?? true) {
        if (currentLocation2.search !== next2.search) {
          const currentSearchEmpty = !currentLocation2.search || typeof currentLocation2.search === "object" && Object.keys(currentLocation2.search).length === 0;
          const nextSearchEmpty = !next2.search || typeof next2.search === "object" && Object.keys(next2.search).length === 0;
          if (!(currentSearchEmpty && nextSearchEmpty)) {
            if (!deepEqual(currentLocation2.search, next2.search, {
              partial: !exact,
              ignoreUndefined: !activeOptions?.explicitUndefined
            })) return false;
          }
        }
      }
      if (activeOptions?.includeHash) return false;
      return true;
    })();
    if (externalLink2) return {
      ...propsSafeToSpread,
      ref: innerRef,
      href: externalLink2,
      ...children && { children },
      ...target && { target },
      ...disabled && { disabled },
      ...style && { style },
      ...className && { className }
    };
    const resolvedActiveProps2 = isActive2 ? functionalUpdate$1(activeProps, {}) ?? STATIC_ACTIVE_OBJECT : STATIC_EMPTY_OBJECT;
    const resolvedInactiveProps2 = isActive2 ? STATIC_EMPTY_OBJECT : functionalUpdate$1(inactiveProps, {}) ?? STATIC_EMPTY_OBJECT;
    const resolvedStyle2 = (() => {
      const baseStyle = style;
      const activeStyle = resolvedActiveProps2.style;
      const inactiveStyle = resolvedInactiveProps2.style;
      if (!baseStyle && !activeStyle && !inactiveStyle) return;
      if (baseStyle && !activeStyle && !inactiveStyle) return baseStyle;
      if (!baseStyle && activeStyle && !inactiveStyle) return activeStyle;
      if (!baseStyle && !activeStyle && inactiveStyle) return inactiveStyle;
      return {
        ...baseStyle,
        ...activeStyle,
        ...inactiveStyle
      };
    })();
    const resolvedClassName2 = (() => {
      const baseClassName = className;
      const activeClassName = resolvedActiveProps2.className;
      const inactiveClassName = resolvedInactiveProps2.className;
      if (!baseClassName && !activeClassName && !inactiveClassName) return "";
      let out = "";
      if (baseClassName) out = baseClassName;
      if (activeClassName) out = out ? `${out} ${activeClassName}` : activeClassName;
      if (inactiveClassName) out = out ? `${out} ${inactiveClassName}` : inactiveClassName;
      return out;
    })();
    return {
      ...propsSafeToSpread,
      ...resolvedActiveProps2,
      ...resolvedInactiveProps2,
      href: hrefOption2?.href,
      ref: innerRef,
      disabled: !!disabled,
      target,
      ...resolvedStyle2 && { style: resolvedStyle2 },
      ...resolvedClassName2 && { className: resolvedClassName2 },
      ...disabled && STATIC_DISABLED_PROPS,
      ...isActive2 && STATIC_ACTIVE_PROPS
    };
  }
}
var STATIC_EMPTY_OBJECT = {};
var STATIC_ACTIVE_OBJECT = { className: "active" };
var STATIC_DISABLED_PROPS = {
  role: "link",
  "aria-disabled": true
};
var STATIC_ACTIVE_PROPS = {
  "data-status": "active",
  "aria-current": "page"
};
function getHrefOption(publicHref, external, history, disabled) {
  if (disabled) return void 0;
  if (external) return {
    href: publicHref,
    external: true
  };
  return {
    href: history.createHref(publicHref) || "/",
    external: false
  };
}
function isSafeInternal(to) {
  if (typeof to !== "string") return false;
  const zero = to.charCodeAt(0);
  if (zero === 47) return to.charCodeAt(1) !== 47;
  return zero === 46;
}
var Link = reactExports.forwardRef((props, ref2) => {
  const { _asChild, ...rest } = props;
  const { type: _type, ...linkProps } = useLinkProps(rest, ref2);
  const children = typeof rest.children === "function" ? rest.children({ isActive: linkProps["data-status"] === "active" }) : rest.children;
  if (!_asChild) {
    const { disabled: _, ...rest2 } = linkProps;
    return reactExports.createElement("a", rest2, children);
  }
  return reactExports.createElement(_asChild, linkProps, children);
});
var Route$b = class Route extends BaseRoute {
  /**
  * @deprecated Use the `createRoute` function instead.
  */
  constructor(options) {
    super(options);
    this.useMatch = (opts) => {
      return useMatch({
        select: opts?.select,
        from: this.id,
        structuralSharing: opts?.structuralSharing
      });
    };
    this.useRouteContext = (opts) => {
      return useRouteContext({
        ...opts,
        from: this.id
      });
    };
    this.useSearch = (opts) => {
      return useSearch({
        select: opts?.select,
        structuralSharing: opts?.structuralSharing,
        from: this.id
      });
    };
    this.useParams = (opts) => {
      return useParams({
        select: opts?.select,
        structuralSharing: opts?.structuralSharing,
        from: this.id
      });
    };
    this.useLoaderDeps = (opts) => {
      return useLoaderDeps({
        ...opts,
        from: this.id
      });
    };
    this.useLoaderData = (opts) => {
      return useLoaderData({
        ...opts,
        from: this.id
      });
    };
    this.useNavigate = () => {
      return useNavigate({ from: this.fullPath });
    };
    this.Link = React.forwardRef((props, ref2) => {
      return /* @__PURE__ */ jsxRuntimeExports.jsx(Link, {
        ref: ref2,
        from: this.fullPath,
        ...props
      });
    });
  }
};
function createRoute(options) {
  return new Route$b(options);
}
function createRootRouteWithContext() {
  return (options) => {
    return createRootRoute(options);
  };
}
var RootRoute = class extends BaseRootRoute {
  /**
  * @deprecated `RootRoute` is now an internal implementation detail. Use `createRootRoute()` instead.
  */
  constructor(options) {
    super(options);
    this.useMatch = (opts) => {
      return useMatch({
        select: opts?.select,
        from: this.id,
        structuralSharing: opts?.structuralSharing
      });
    };
    this.useRouteContext = (opts) => {
      return useRouteContext({
        ...opts,
        from: this.id
      });
    };
    this.useSearch = (opts) => {
      return useSearch({
        select: opts?.select,
        structuralSharing: opts?.structuralSharing,
        from: this.id
      });
    };
    this.useParams = (opts) => {
      return useParams({
        select: opts?.select,
        structuralSharing: opts?.structuralSharing,
        from: this.id
      });
    };
    this.useLoaderDeps = (opts) => {
      return useLoaderDeps({
        ...opts,
        from: this.id
      });
    };
    this.useLoaderData = (opts) => {
      return useLoaderData({
        ...opts,
        from: this.id
      });
    };
    this.useNavigate = () => {
      return useNavigate({ from: this.fullPath });
    };
    this.Link = React.forwardRef((props, ref2) => {
      return /* @__PURE__ */ jsxRuntimeExports.jsx(Link, {
        ref: ref2,
        from: this.fullPath,
        ...props
      });
    });
  }
};
function createRootRoute(options) {
  return new RootRoute(options);
}
function createFileRoute(path) {
  return new FileRoute(path, { silent: true }).createRoute;
}
var FileRoute = class {
  constructor(path, _opts) {
    this.path = path;
    this.createRoute = (options) => {
      const route = createRoute(options);
      route.isRoot = false;
      return route;
    };
    this.silent = _opts?.silent;
  }
};
function lazyRouteComponent(importer, exportName) {
  let loadPromise;
  let comp;
  let error;
  let reload;
  const load = () => {
    if (!loadPromise) loadPromise = importer().then((res) => {
      loadPromise = void 0;
      comp = res[exportName];
    }).catch((err) => {
      error = err;
      if (isModuleNotFoundError(error)) {
        if (error instanceof Error && typeof window !== "undefined" && typeof sessionStorage !== "undefined") {
          const storageKey = `tanstack_router_reload:${error.message}`;
          if (!sessionStorage.getItem(storageKey)) {
            sessionStorage.setItem(storageKey, "1");
            reload = true;
          }
        }
      }
    });
    return loadPromise;
  };
  const lazyComp = function Lazy(props) {
    if (reload) {
      window.location.reload();
      throw new Promise(() => {
      });
    }
    if (error) throw error;
    if (!comp) if (reactUse) reactUse(load());
    else throw load();
    return reactExports.createElement(comp, props);
  };
  lazyComp.preload = load;
  return lazyComp;
}
var getStoreFactory = (opts) => {
  return {
    createMutableStore: createNonReactiveMutableStore,
    createReadonlyStore: createNonReactiveReadonlyStore,
    batch: (fn) => fn()
  };
};
var createRouter = (options) => {
  return new Router(options);
};
var Router = class extends RouterCore {
  constructor(options) {
    super(options, getStoreFactory);
  }
};
function Asset(asset) {
  const { attrs, children, nonce } = asset;
  switch (asset.tag) {
    case "title":
      return /* @__PURE__ */ jsxRuntimeExports.jsx("title", {
        ...attrs,
        suppressHydrationWarning: true,
        children
      });
    case "meta":
      return /* @__PURE__ */ jsxRuntimeExports.jsx("meta", {
        ...attrs,
        suppressHydrationWarning: true
      });
    case "link":
      return /* @__PURE__ */ jsxRuntimeExports.jsx("link", {
        ...attrs,
        precedence: attrs?.precedence ?? (attrs?.rel === "stylesheet" ? "default" : void 0),
        nonce,
        suppressHydrationWarning: true
      });
    case "style":
      if (asset.inlineCss && false) ;
      return /* @__PURE__ */ jsxRuntimeExports.jsx("style", {
        ...attrs,
        dangerouslySetInnerHTML: { __html: children },
        nonce
      });
    case "script":
      return /* @__PURE__ */ jsxRuntimeExports.jsx(Script, {
        attrs,
        children
      });
    default:
      return null;
  }
}
function Script({ attrs, children }) {
  useRouter();
  useHydrated();
  const dataScript = typeof attrs?.type === "string" && attrs.type !== "" && attrs.type !== "text/javascript" && attrs.type !== "module";
  reactExports.useEffect(() => {
    if (dataScript) return;
    if (attrs?.src) {
      const normSrc = (() => {
        try {
          const base = document.baseURI || window.location.href;
          return new URL(attrs.src, base).href;
        } catch {
          return attrs.src;
        }
      })();
      if (Array.from(document.querySelectorAll("script[src]")).find((el) => el.src === normSrc)) return;
      const script = document.createElement("script");
      for (const [key, value] of Object.entries(attrs)) if (key !== "suppressHydrationWarning" && value !== void 0 && value !== false) script.setAttribute(key, typeof value === "boolean" ? "" : String(value));
      document.head.appendChild(script);
      return () => {
        if (script.parentNode) script.parentNode.removeChild(script);
      };
    }
    if (typeof children === "string") {
      const typeAttr = typeof attrs?.type === "string" ? attrs.type : "text/javascript";
      const nonceAttr = typeof attrs?.nonce === "string" ? attrs.nonce : void 0;
      if (Array.from(document.querySelectorAll("script:not([src])")).find((el) => {
        if (!(el instanceof HTMLScriptElement)) return false;
        const sType = el.getAttribute("type") ?? "text/javascript";
        const sNonce = el.getAttribute("nonce") ?? void 0;
        return el.textContent === children && sType === typeAttr && sNonce === nonceAttr;
      })) return;
      const script = document.createElement("script");
      script.textContent = children;
      if (attrs) {
        for (const [key, value] of Object.entries(attrs)) if (key !== "suppressHydrationWarning" && value !== void 0 && value !== false) script.setAttribute(key, typeof value === "boolean" ? "" : String(value));
      }
      document.head.appendChild(script);
      return () => {
        if (script.parentNode) script.parentNode.removeChild(script);
      };
    }
  }, [
    attrs,
    children,
    dataScript
  ]);
  {
    if (attrs?.src) return /* @__PURE__ */ jsxRuntimeExports.jsx("script", {
      ...attrs,
      suppressHydrationWarning: true
    });
    if (typeof children === "string") return /* @__PURE__ */ jsxRuntimeExports.jsx("script", {
      ...attrs,
      dangerouslySetInnerHTML: { __html: children },
      suppressHydrationWarning: true
    });
    return null;
  }
}
function buildTagsFromMatches(router2, nonce, matches, assetCrossOrigin) {
  const routeMeta = matches.map((match) => match.meta).filter(Boolean);
  const resultMeta = [];
  const metaByAttribute = {};
  let title;
  for (let i = routeMeta.length - 1; i >= 0; i--) {
    const metas = routeMeta[i];
    for (let j = metas.length - 1; j >= 0; j--) {
      const m = metas[j];
      if (!m) continue;
      if (m.title) {
        if (!title) title = {
          tag: "title",
          children: m.title
        };
      } else if ("script:ld+json" in m) try {
        const json = JSON.stringify(m["script:ld+json"]);
        resultMeta.push({
          tag: "script",
          attrs: { type: "application/ld+json" },
          children: escapeHtml(json)
        });
      } catch {
      }
      else {
        const attribute = m.name ?? m.property;
        if (attribute) if (metaByAttribute[attribute]) continue;
        else metaByAttribute[attribute] = true;
        resultMeta.push({
          tag: "meta",
          attrs: {
            ...m,
            nonce
          }
        });
      }
    }
  }
  if (title) resultMeta.push(title);
  if (nonce) resultMeta.push({
    tag: "meta",
    attrs: {
      property: "csp-nonce",
      content: nonce
    }
  });
  resultMeta.reverse();
  const constructedLinks = matches.map((match) => match.links).filter(Boolean).flat(1).map((link) => ({
    tag: "link",
    attrs: {
      ...link,
      nonce
    }
  }));
  const manifest = router2.ssr?.manifest;
  const assetLinks = matches.map((match) => manifest?.routes[match.routeId]?.assets ?? []).filter(Boolean).flat(1).flatMap((asset) => {
    if (asset.tag === "link") {
      if (isInlinableStylesheet(manifest, asset)) return [];
      return [{
        tag: "link",
        attrs: {
          ...asset.attrs,
          crossOrigin: getAssetCrossOrigin(assetCrossOrigin, "stylesheet") ?? asset.attrs?.crossOrigin,
          suppressHydrationWarning: true,
          nonce
        }
      }];
    }
    if (asset.tag === "style") return [{
      tag: "style",
      attrs: {
        ...asset.attrs,
        nonce
      },
      children: asset.children,
      ...asset.inlineCss ? { inlineCss: true } : {}
    }];
    return [];
  });
  const preloadLinks = [];
  matches.map((match) => router2.looseRoutesById[match.routeId]).forEach((route) => router2.ssr?.manifest?.routes[route.id]?.preloads?.filter(Boolean).forEach((preload) => {
    const preloadLink = resolveManifestAssetLink(preload);
    preloadLinks.push({
      tag: "link",
      attrs: {
        rel: "modulepreload",
        href: preloadLink.href,
        crossOrigin: getAssetCrossOrigin(assetCrossOrigin, "modulepreload") ?? preloadLink.crossOrigin,
        nonce
      }
    });
  }));
  const styles = matches.map((match) => match.styles).flat(1).filter(Boolean).map(({ children, ...attrs }) => ({
    tag: "style",
    attrs: {
      ...attrs,
      nonce
    },
    children
  }));
  const headScripts = matches.map((match) => match.headScripts).flat(1).filter(Boolean).map(({ children, ...script }) => ({
    tag: "script",
    attrs: {
      ...script,
      nonce
    },
    children
  }));
  return uniqBy([
    ...resultMeta,
    ...preloadLinks,
    ...constructedLinks,
    ...assetLinks,
    ...styles,
    ...headScripts
  ], (d) => JSON.stringify(d));
}
var useTags = (assetCrossOrigin) => {
  const router2 = useRouter();
  const nonce = router2.options.ssr?.nonce;
  return buildTagsFromMatches(router2, nonce, router2.stores.matches.get(), assetCrossOrigin);
};
function uniqBy(arr, fn) {
  const seen = /* @__PURE__ */ new Set();
  return arr.filter((item) => {
    const key = fn(item);
    if (seen.has(key)) return false;
    seen.add(key);
    return true;
  });
}
function HeadContent(props) {
  const tags = useTags(props.assetCrossOrigin);
  const nonce = useRouter().options.ssr?.nonce;
  return /* @__PURE__ */ jsxRuntimeExports.jsx(jsxRuntimeExports.Fragment, { children: tags.map((tag) => /* @__PURE__ */ reactExports.createElement(Asset, {
    ...tag,
    key: `tsr-meta-${JSON.stringify(tag)}`,
    nonce
  })) });
}
var Scripts = () => {
  const router2 = useRouter();
  const nonce = router2.options.ssr?.nonce;
  const getAssetScripts = (matches) => {
    const assetScripts = [];
    const manifest = router2.ssr?.manifest;
    if (!manifest) return [];
    matches.map((match) => router2.looseRoutesById[match.routeId]).forEach((route) => manifest.routes[route.id]?.assets?.filter((d) => d.tag === "script").forEach((asset) => {
      assetScripts.push({
        tag: "script",
        attrs: {
          ...asset.attrs,
          nonce
        },
        children: asset.children
      });
    }));
    return assetScripts;
  };
  const getScripts = (matches) => matches.map((match) => match.scripts).flat(1).filter(Boolean).map(({ children, ...script }) => ({
    tag: "script",
    attrs: {
      ...script,
      suppressHydrationWarning: true,
      nonce
    },
    children
  }));
  {
    const activeMatches = router2.stores.matches.get();
    const assetScripts = getAssetScripts(activeMatches);
    return renderScripts(router2, getScripts(activeMatches), assetScripts);
  }
};
function renderScripts(router2, scripts, assetScripts) {
  let serverBufferedScript = void 0;
  if (router2.serverSsr) serverBufferedScript = router2.serverSsr.takeBufferedScripts();
  const allScripts = [...scripts, ...assetScripts];
  if (serverBufferedScript) allScripts.unshift(serverBufferedScript);
  return /* @__PURE__ */ jsxRuntimeExports.jsx(jsxRuntimeExports.Fragment, { children: allScripts.map((asset, i) => /* @__PURE__ */ reactExports.createElement(Asset, {
    ...asset,
    key: `tsr-scripts-${asset.tag}-${i}`
  })) });
}
var Subscribable = class {
  constructor() {
    this.listeners = /* @__PURE__ */ new Set();
    this.subscribe = this.subscribe.bind(this);
  }
  subscribe(listener) {
    this.listeners.add(listener);
    this.onSubscribe();
    return () => {
      this.listeners.delete(listener);
      this.onUnsubscribe();
    };
  }
  hasListeners() {
    return this.listeners.size > 0;
  }
  onSubscribe() {
  }
  onUnsubscribe() {
  }
};
var FocusManager = class extends Subscribable {
  #focused;
  #cleanup;
  #setup;
  constructor() {
    super();
    this.#setup = (onFocus) => {
      if (typeof window !== "undefined" && window.addEventListener) {
        const listener = () => onFocus();
        window.addEventListener("visibilitychange", listener, false);
        return () => {
          window.removeEventListener("visibilitychange", listener);
        };
      }
      return;
    };
  }
  onSubscribe() {
    if (!this.#cleanup) {
      this.setEventListener(this.#setup);
    }
  }
  onUnsubscribe() {
    if (!this.hasListeners()) {
      this.#cleanup?.();
      this.#cleanup = void 0;
    }
  }
  setEventListener(setup) {
    this.#setup = setup;
    this.#cleanup?.();
    this.#cleanup = setup((focused) => {
      if (typeof focused === "boolean") {
        this.setFocused(focused);
      } else {
        this.onFocus();
      }
    });
  }
  setFocused(focused) {
    const changed = this.#focused !== focused;
    if (changed) {
      this.#focused = focused;
      this.onFocus();
    }
  }
  onFocus() {
    const isFocused = this.isFocused();
    this.listeners.forEach((listener) => {
      listener(isFocused);
    });
  }
  isFocused() {
    if (typeof this.#focused === "boolean") {
      return this.#focused;
    }
    return globalThis.document?.visibilityState !== "hidden";
  }
};
var focusManager = new FocusManager();
var defaultTimeoutProvider = {
  // We need the wrapper function syntax below instead of direct references to
  // global setTimeout etc.
  //
  // BAD: `setTimeout: setTimeout`
  // GOOD: `setTimeout: (cb, delay) => setTimeout(cb, delay)`
  //
  // If we use direct references here, then anything that wants to spy on or
  // replace the global setTimeout (like tests) won't work since we'll already
  // have a hard reference to the original implementation at the time when this
  // file was imported.
  setTimeout: (callback, delay) => setTimeout(callback, delay),
  clearTimeout: (timeoutId) => clearTimeout(timeoutId),
  setInterval: (callback, delay) => setInterval(callback, delay),
  clearInterval: (intervalId) => clearInterval(intervalId)
};
var TimeoutManager = class {
  // We cannot have TimeoutManager<T> as we must instantiate it with a concrete
  // type at app boot; and if we leave that type, then any new timer provider
  // would need to support the default provider's concrete timer ID, which is
  // infeasible across environments.
  //
  // We settle for type safety for the TimeoutProvider type, and accept that
  // this class is unsafe internally to allow for extension.
  #provider = defaultTimeoutProvider;
  #providerCalled = false;
  setTimeoutProvider(provider) {
    this.#provider = provider;
  }
  setTimeout(callback, delay) {
    return this.#provider.setTimeout(callback, delay);
  }
  clearTimeout(timeoutId) {
    this.#provider.clearTimeout(timeoutId);
  }
  setInterval(callback, delay) {
    return this.#provider.setInterval(callback, delay);
  }
  clearInterval(intervalId) {
    this.#provider.clearInterval(intervalId);
  }
};
var timeoutManager = new TimeoutManager();
function systemSetTimeoutZero(callback) {
  setTimeout(callback, 0);
}
var isServer = typeof window === "undefined" || "Deno" in globalThis;
function noop$1() {
}
function functionalUpdate(updater, input) {
  return typeof updater === "function" ? updater(input) : updater;
}
function isValidTimeout(value) {
  return typeof value === "number" && value >= 0 && value !== Infinity;
}
function timeUntilStale(updatedAt, staleTime) {
  return Math.max(updatedAt + (staleTime || 0) - Date.now(), 0);
}
function resolveStaleTime(staleTime, query) {
  return typeof staleTime === "function" ? staleTime(query) : staleTime;
}
function resolveQueryBoolean(option, query) {
  return typeof option === "function" ? option(query) : option;
}
function matchQuery(filters, query) {
  const {
    type: type2 = "all",
    exact,
    fetchStatus,
    predicate,
    queryKey,
    stale
  } = filters;
  if (queryKey) {
    if (exact) {
      if (query.queryHash !== hashQueryKeyByOptions(queryKey, query.options)) {
        return false;
      }
    } else if (!partialMatchKey(query.queryKey, queryKey)) {
      return false;
    }
  }
  if (type2 !== "all") {
    const isActive = query.isActive();
    if (type2 === "active" && !isActive) {
      return false;
    }
    if (type2 === "inactive" && isActive) {
      return false;
    }
  }
  if (typeof stale === "boolean" && query.isStale() !== stale) {
    return false;
  }
  if (fetchStatus && fetchStatus !== query.state.fetchStatus) {
    return false;
  }
  if (predicate && !predicate(query)) {
    return false;
  }
  return true;
}
function matchMutation(filters, mutation) {
  const { exact, status, predicate, mutationKey } = filters;
  if (mutationKey) {
    if (!mutation.options.mutationKey) {
      return false;
    }
    if (exact) {
      if (hashKey(mutation.options.mutationKey) !== hashKey(mutationKey)) {
        return false;
      }
    } else if (!partialMatchKey(mutation.options.mutationKey, mutationKey)) {
      return false;
    }
  }
  if (status && mutation.state.status !== status) {
    return false;
  }
  if (predicate && !predicate(mutation)) {
    return false;
  }
  return true;
}
function hashQueryKeyByOptions(queryKey, options) {
  const hashFn = options?.queryKeyHashFn || hashKey;
  return hashFn(queryKey);
}
function hashKey(queryKey) {
  return JSON.stringify(
    queryKey,
    (_, val) => isPlainObject$1(val) ? Object.keys(val).sort().reduce((result, key) => {
      result[key] = val[key];
      return result;
    }, {}) : val
  );
}
function partialMatchKey(a, b) {
  if (a === b) {
    return true;
  }
  if (typeof a !== typeof b) {
    return false;
  }
  if (a && b && typeof a === "object" && typeof b === "object") {
    return Object.keys(b).every((key) => partialMatchKey(a[key], b[key]));
  }
  return false;
}
var hasOwn = Object.prototype.hasOwnProperty;
function replaceEqualDeep(a, b, depth = 0) {
  if (a === b) {
    return a;
  }
  if (depth > 500) return b;
  const array = isPlainArray(a) && isPlainArray(b);
  if (!array && !(isPlainObject$1(a) && isPlainObject$1(b))) return b;
  const aItems = array ? a : Object.keys(a);
  const aSize = aItems.length;
  const bItems = array ? b : Object.keys(b);
  const bSize = bItems.length;
  const copy = array ? new Array(bSize) : {};
  let equalItems = 0;
  for (let i = 0; i < bSize; i++) {
    const key = array ? i : bItems[i];
    const aItem = a[key];
    const bItem = b[key];
    if (aItem === bItem) {
      copy[key] = aItem;
      if (array ? i < aSize : hasOwn.call(a, key)) equalItems++;
      continue;
    }
    if (aItem === null || bItem === null || typeof aItem !== "object" || typeof bItem !== "object") {
      copy[key] = bItem;
      continue;
    }
    const v = replaceEqualDeep(aItem, bItem, depth + 1);
    copy[key] = v;
    if (v === aItem) equalItems++;
  }
  return aSize === bSize && equalItems === aSize ? a : copy;
}
function shallowEqualObjects(a, b) {
  if (!b || Object.keys(a).length !== Object.keys(b).length) {
    return false;
  }
  for (const key in a) {
    if (a[key] !== b[key]) {
      return false;
    }
  }
  return true;
}
function isPlainArray(value) {
  return Array.isArray(value) && value.length === Object.keys(value).length;
}
function isPlainObject$1(o) {
  if (!hasObjectPrototype(o)) {
    return false;
  }
  const ctor = o.constructor;
  if (ctor === void 0) {
    return true;
  }
  const prot = ctor.prototype;
  if (!hasObjectPrototype(prot)) {
    return false;
  }
  if (!prot.hasOwnProperty("isPrototypeOf")) {
    return false;
  }
  if (Object.getPrototypeOf(o) !== Object.prototype) {
    return false;
  }
  return true;
}
function hasObjectPrototype(o) {
  return Object.prototype.toString.call(o) === "[object Object]";
}
function sleep(timeout) {
  return new Promise((resolve2) => {
    timeoutManager.setTimeout(resolve2, timeout);
  });
}
function replaceData(prevData, data, options) {
  if (typeof options.structuralSharing === "function") {
    return options.structuralSharing(prevData, data);
  } else if (options.structuralSharing !== false) {
    return replaceEqualDeep(prevData, data);
  }
  return data;
}
function addToEnd(items, item, max2 = 0) {
  const newItems = [...items, item];
  return max2 && newItems.length > max2 ? newItems.slice(1) : newItems;
}
function addToStart(items, item, max2 = 0) {
  const newItems = [item, ...items];
  return max2 && newItems.length > max2 ? newItems.slice(0, -1) : newItems;
}
var skipToken = /* @__PURE__ */ Symbol();
function ensureQueryFn(options, fetchOptions) {
  if (!options.queryFn && fetchOptions?.initialPromise) {
    return () => fetchOptions.initialPromise;
  }
  if (!options.queryFn || options.queryFn === skipToken) {
    return () => Promise.reject(new Error(`Missing queryFn: '${options.queryHash}'`));
  }
  return options.queryFn;
}
function shouldThrowError(throwOnError, params) {
  if (typeof throwOnError === "function") {
    return throwOnError(...params);
  }
  return !!throwOnError;
}
function addConsumeAwareSignal(object, getSignal, onCancelled) {
  let consumed = false;
  let signal;
  Object.defineProperty(object, "signal", {
    enumerable: true,
    get: () => {
      signal ??= getSignal();
      if (consumed) {
        return signal;
      }
      consumed = true;
      if (signal.aborted) {
        onCancelled();
      } else {
        signal.addEventListener("abort", onCancelled, { once: true });
      }
      return signal;
    }
  });
  return object;
}
var environmentManager = /* @__PURE__ */ (() => {
  let isServerFn = () => isServer;
  return {
    /**
     * Returns whether the current runtime should be treated as a server environment.
     */
    isServer() {
      return isServerFn();
    },
    /**
     * Overrides the server check globally.
     */
    setIsServer(isServerValue) {
      isServerFn = isServerValue;
    }
  };
})();
function pendingThenable() {
  let resolve2;
  let reject;
  const thenable = new Promise((_resolve, _reject) => {
    resolve2 = _resolve;
    reject = _reject;
  });
  thenable.status = "pending";
  thenable.catch(() => {
  });
  function finalize(data) {
    Object.assign(thenable, data);
    delete thenable.resolve;
    delete thenable.reject;
  }
  thenable.resolve = (value) => {
    finalize({
      status: "fulfilled",
      value
    });
    resolve2(value);
  };
  thenable.reject = (reason) => {
    finalize({
      status: "rejected",
      reason
    });
    reject(reason);
  };
  return thenable;
}
var defaultScheduler = systemSetTimeoutZero;
function createNotifyManager() {
  let queue = [];
  let transactions = 0;
  let notifyFn = (callback) => {
    callback();
  };
  let batchNotifyFn = (callback) => {
    callback();
  };
  let scheduleFn = defaultScheduler;
  const schedule = (callback) => {
    if (transactions) {
      queue.push(callback);
    } else {
      scheduleFn(() => {
        notifyFn(callback);
      });
    }
  };
  const flush = () => {
    const originalQueue = queue;
    queue = [];
    if (originalQueue.length) {
      scheduleFn(() => {
        batchNotifyFn(() => {
          originalQueue.forEach((callback) => {
            notifyFn(callback);
          });
        });
      });
    }
  };
  return {
    batch: (callback) => {
      let result;
      transactions++;
      try {
        result = callback();
      } finally {
        transactions--;
        if (!transactions) {
          flush();
        }
      }
      return result;
    },
    /**
     * All calls to the wrapped function will be batched.
     */
    batchCalls: (callback) => {
      return (...args) => {
        schedule(() => {
          callback(...args);
        });
      };
    },
    schedule,
    /**
     * Use this method to set a custom notify function.
     * This can be used to for example wrap notifications with `React.act` while running tests.
     */
    setNotifyFunction: (fn) => {
      notifyFn = fn;
    },
    /**
     * Use this method to set a custom function to batch notifications together into a single tick.
     * By default React Query will use the batch function provided by ReactDOM or React Native.
     */
    setBatchNotifyFunction: (fn) => {
      batchNotifyFn = fn;
    },
    setScheduler: (fn) => {
      scheduleFn = fn;
    }
  };
}
var notifyManager = createNotifyManager();
var OnlineManager = class extends Subscribable {
  #online = true;
  #cleanup;
  #setup;
  constructor() {
    super();
    this.#setup = (onOnline) => {
      if (typeof window !== "undefined" && window.addEventListener) {
        const onlineListener = () => onOnline(true);
        const offlineListener = () => onOnline(false);
        window.addEventListener("online", onlineListener, false);
        window.addEventListener("offline", offlineListener, false);
        return () => {
          window.removeEventListener("online", onlineListener);
          window.removeEventListener("offline", offlineListener);
        };
      }
      return;
    };
  }
  onSubscribe() {
    if (!this.#cleanup) {
      this.setEventListener(this.#setup);
    }
  }
  onUnsubscribe() {
    if (!this.hasListeners()) {
      this.#cleanup?.();
      this.#cleanup = void 0;
    }
  }
  setEventListener(setup) {
    this.#setup = setup;
    this.#cleanup?.();
    this.#cleanup = setup(this.setOnline.bind(this));
  }
  setOnline(online) {
    const changed = this.#online !== online;
    if (changed) {
      this.#online = online;
      this.listeners.forEach((listener) => {
        listener(online);
      });
    }
  }
  isOnline() {
    return this.#online;
  }
};
var onlineManager = new OnlineManager();
function defaultRetryDelay(failureCount) {
  return Math.min(1e3 * 2 ** failureCount, 3e4);
}
function canFetch(networkMode) {
  return (networkMode ?? "online") === "online" ? onlineManager.isOnline() : true;
}
var CancelledError = class extends Error {
  constructor(options) {
    super("CancelledError");
    this.revert = options?.revert;
    this.silent = options?.silent;
  }
};
function createRetryer(config) {
  let isRetryCancelled = false;
  let failureCount = 0;
  let continueFn;
  const thenable = pendingThenable();
  const isResolved = () => thenable.status !== "pending";
  const cancel = (cancelOptions) => {
    if (!isResolved()) {
      const error = new CancelledError(cancelOptions);
      reject(error);
      config.onCancel?.(error);
    }
  };
  const cancelRetry = () => {
    isRetryCancelled = true;
  };
  const continueRetry = () => {
    isRetryCancelled = false;
  };
  const canContinue = () => focusManager.isFocused() && (config.networkMode === "always" || onlineManager.isOnline()) && config.canRun();
  const canStart = () => canFetch(config.networkMode) && config.canRun();
  const resolve2 = (value) => {
    if (!isResolved()) {
      continueFn?.();
      thenable.resolve(value);
    }
  };
  const reject = (value) => {
    if (!isResolved()) {
      continueFn?.();
      thenable.reject(value);
    }
  };
  const pause = () => {
    return new Promise((continueResolve) => {
      continueFn = (value) => {
        if (isResolved() || canContinue()) {
          continueResolve(value);
        }
      };
      config.onPause?.();
    }).then(() => {
      continueFn = void 0;
      if (!isResolved()) {
        config.onContinue?.();
      }
    });
  };
  const run = () => {
    if (isResolved()) {
      return;
    }
    let promiseOrValue;
    const initialPromise = failureCount === 0 ? config.initialPromise : void 0;
    try {
      promiseOrValue = initialPromise ?? config.fn();
    } catch (error) {
      promiseOrValue = Promise.reject(error);
    }
    Promise.resolve(promiseOrValue).then(resolve2).catch((error) => {
      if (isResolved()) {
        return;
      }
      const retry = config.retry ?? (environmentManager.isServer() ? 0 : 3);
      const retryDelay = config.retryDelay ?? defaultRetryDelay;
      const delay = typeof retryDelay === "function" ? retryDelay(failureCount, error) : retryDelay;
      const shouldRetry = retry === true || typeof retry === "number" && failureCount < retry || typeof retry === "function" && retry(failureCount, error);
      if (isRetryCancelled || !shouldRetry) {
        reject(error);
        return;
      }
      failureCount++;
      config.onFail?.(failureCount, error);
      sleep(delay).then(() => {
        return canContinue() ? void 0 : pause();
      }).then(() => {
        if (isRetryCancelled) {
          reject(error);
        } else {
          run();
        }
      });
    });
  };
  return {
    promise: thenable,
    status: () => thenable.status,
    cancel,
    continue: () => {
      continueFn?.();
      return thenable;
    },
    cancelRetry,
    continueRetry,
    canStart,
    start: () => {
      if (canStart()) {
        run();
      } else {
        pause().then(run);
      }
      return thenable;
    }
  };
}
var Removable = class {
  #gcTimeout;
  destroy() {
    this.clearGcTimeout();
  }
  scheduleGc() {
    this.clearGcTimeout();
    if (isValidTimeout(this.gcTime)) {
      this.#gcTimeout = timeoutManager.setTimeout(() => {
        this.optionalRemove();
      }, this.gcTime);
    }
  }
  updateGcTime(newGcTime) {
    this.gcTime = Math.max(
      this.gcTime || 0,
      newGcTime ?? (environmentManager.isServer() ? Infinity : 5 * 60 * 1e3)
    );
  }
  clearGcTimeout() {
    if (this.#gcTimeout !== void 0) {
      timeoutManager.clearTimeout(this.#gcTimeout);
      this.#gcTimeout = void 0;
    }
  }
};
var Query = class extends Removable {
  #initialState;
  #revertState;
  #cache;
  #client;
  #retryer;
  #defaultOptions;
  #abortSignalConsumed;
  constructor(config) {
    super();
    this.#abortSignalConsumed = false;
    this.#defaultOptions = config.defaultOptions;
    this.setOptions(config.options);
    this.observers = [];
    this.#client = config.client;
    this.#cache = this.#client.getQueryCache();
    this.queryKey = config.queryKey;
    this.queryHash = config.queryHash;
    this.#initialState = getDefaultState$1(this.options);
    this.state = config.state ?? this.#initialState;
    this.scheduleGc();
  }
  get meta() {
    return this.options.meta;
  }
  get promise() {
    return this.#retryer?.promise;
  }
  setOptions(options) {
    this.options = { ...this.#defaultOptions, ...options };
    this.updateGcTime(this.options.gcTime);
    if (this.state && this.state.data === void 0) {
      const defaultState = getDefaultState$1(this.options);
      if (defaultState.data !== void 0) {
        this.setState(
          successState(defaultState.data, defaultState.dataUpdatedAt)
        );
        this.#initialState = defaultState;
      }
    }
  }
  optionalRemove() {
    if (!this.observers.length && this.state.fetchStatus === "idle") {
      this.#cache.remove(this);
    }
  }
  setData(newData, options) {
    const data = replaceData(this.state.data, newData, this.options);
    this.#dispatch({
      data,
      type: "success",
      dataUpdatedAt: options?.updatedAt,
      manual: options?.manual
    });
    return data;
  }
  setState(state, setStateOptions) {
    this.#dispatch({ type: "setState", state, setStateOptions });
  }
  cancel(options) {
    const promise = this.#retryer?.promise;
    this.#retryer?.cancel(options);
    return promise ? promise.then(noop$1).catch(noop$1) : Promise.resolve();
  }
  destroy() {
    super.destroy();
    this.cancel({ silent: true });
  }
  get resetState() {
    return this.#initialState;
  }
  reset() {
    this.destroy();
    this.setState(this.resetState);
  }
  isActive() {
    return this.observers.some(
      (observer) => resolveQueryBoolean(observer.options.enabled, this) !== false
    );
  }
  isDisabled() {
    if (this.getObserversCount() > 0) {
      return !this.isActive();
    }
    return this.options.queryFn === skipToken || !this.isFetched();
  }
  isFetched() {
    return this.state.dataUpdateCount + this.state.errorUpdateCount > 0;
  }
  isStatic() {
    if (this.getObserversCount() > 0) {
      return this.observers.some(
        (observer) => resolveStaleTime(observer.options.staleTime, this) === "static"
      );
    }
    return false;
  }
  isStale() {
    if (this.getObserversCount() > 0) {
      return this.observers.some(
        (observer) => observer.getCurrentResult().isStale
      );
    }
    return this.state.data === void 0 || this.state.isInvalidated;
  }
  isStaleByTime(staleTime = 0) {
    if (this.state.data === void 0) {
      return true;
    }
    if (staleTime === "static") {
      return false;
    }
    if (this.state.isInvalidated) {
      return true;
    }
    return !timeUntilStale(this.state.dataUpdatedAt, staleTime);
  }
  onFocus() {
    const observer = this.observers.find((x) => x.shouldFetchOnWindowFocus());
    observer?.refetch({ cancelRefetch: false });
    this.#retryer?.continue();
  }
  onOnline() {
    const observer = this.observers.find((x) => x.shouldFetchOnReconnect());
    observer?.refetch({ cancelRefetch: false });
    this.#retryer?.continue();
  }
  addObserver(observer) {
    if (!this.observers.includes(observer)) {
      this.observers.push(observer);
      this.clearGcTimeout();
      this.#cache.notify({ type: "observerAdded", query: this, observer });
    }
  }
  removeObserver(observer) {
    if (this.observers.includes(observer)) {
      this.observers = this.observers.filter((x) => x !== observer);
      if (!this.observers.length) {
        if (this.#retryer) {
          if (this.#abortSignalConsumed || this.#isInitialPausedFetch()) {
            this.#retryer.cancel({ revert: true });
          } else {
            this.#retryer.cancelRetry();
          }
        }
        this.scheduleGc();
      }
      this.#cache.notify({ type: "observerRemoved", query: this, observer });
    }
  }
  getObserversCount() {
    return this.observers.length;
  }
  #isInitialPausedFetch() {
    return this.state.fetchStatus === "paused" && this.state.status === "pending";
  }
  invalidate() {
    if (!this.state.isInvalidated) {
      this.#dispatch({ type: "invalidate" });
    }
  }
  async fetch(options, fetchOptions) {
    if (this.state.fetchStatus !== "idle" && // If the promise in the retryer is already rejected, we have to definitely
    // re-start the fetch; there is a chance that the query is still in a
    // pending state when that happens
    this.#retryer?.status() !== "rejected") {
      if (this.state.data !== void 0 && fetchOptions?.cancelRefetch) {
        this.cancel({ silent: true });
      } else if (this.#retryer) {
        this.#retryer.continueRetry();
        return this.#retryer.promise;
      }
    }
    if (options) {
      this.setOptions(options);
    }
    if (!this.options.queryFn) {
      const observer = this.observers.find((x) => x.options.queryFn);
      if (observer) {
        this.setOptions(observer.options);
      }
    }
    const abortController = new AbortController();
    const addSignalProperty = (object) => {
      Object.defineProperty(object, "signal", {
        enumerable: true,
        get: () => {
          this.#abortSignalConsumed = true;
          return abortController.signal;
        }
      });
    };
    const fetchFn = () => {
      const queryFn = ensureQueryFn(this.options, fetchOptions);
      const createQueryFnContext = () => {
        const queryFnContext2 = {
          client: this.#client,
          queryKey: this.queryKey,
          meta: this.meta
        };
        addSignalProperty(queryFnContext2);
        return queryFnContext2;
      };
      const queryFnContext = createQueryFnContext();
      this.#abortSignalConsumed = false;
      if (this.options.persister) {
        return this.options.persister(
          queryFn,
          queryFnContext,
          this
        );
      }
      return queryFn(queryFnContext);
    };
    const createFetchContext = () => {
      const context2 = {
        fetchOptions,
        options: this.options,
        queryKey: this.queryKey,
        client: this.#client,
        state: this.state,
        fetchFn
      };
      addSignalProperty(context2);
      return context2;
    };
    const context = createFetchContext();
    this.options.behavior?.onFetch(context, this);
    this.#revertState = this.state;
    if (this.state.fetchStatus === "idle" || this.state.fetchMeta !== context.fetchOptions?.meta) {
      this.#dispatch({ type: "fetch", meta: context.fetchOptions?.meta });
    }
    this.#retryer = createRetryer({
      initialPromise: fetchOptions?.initialPromise,
      fn: context.fetchFn,
      onCancel: (error) => {
        if (error instanceof CancelledError && error.revert) {
          this.setState({
            ...this.#revertState,
            fetchStatus: "idle"
          });
        }
        abortController.abort();
      },
      onFail: (failureCount, error) => {
        this.#dispatch({ type: "failed", failureCount, error });
      },
      onPause: () => {
        this.#dispatch({ type: "pause" });
      },
      onContinue: () => {
        this.#dispatch({ type: "continue" });
      },
      retry: context.options.retry,
      retryDelay: context.options.retryDelay,
      networkMode: context.options.networkMode,
      canRun: () => true
    });
    try {
      const data = await this.#retryer.start();
      if (data === void 0) {
        if (false) ;
        throw new Error(`${this.queryHash} data is undefined`);
      }
      this.setData(data);
      this.#cache.config.onSuccess?.(data, this);
      this.#cache.config.onSettled?.(
        data,
        this.state.error,
        this
      );
      return data;
    } catch (error) {
      if (error instanceof CancelledError) {
        if (error.silent) {
          return this.#retryer.promise;
        } else if (error.revert) {
          if (this.state.data === void 0) {
            throw error;
          }
          return this.state.data;
        }
      }
      this.#dispatch({
        type: "error",
        error
      });
      this.#cache.config.onError?.(
        error,
        this
      );
      this.#cache.config.onSettled?.(
        this.state.data,
        error,
        this
      );
      throw error;
    } finally {
      this.scheduleGc();
    }
  }
  #dispatch(action) {
    const reducer = (state) => {
      switch (action.type) {
        case "failed":
          return {
            ...state,
            fetchFailureCount: action.failureCount,
            fetchFailureReason: action.error
          };
        case "pause":
          return {
            ...state,
            fetchStatus: "paused"
          };
        case "continue":
          return {
            ...state,
            fetchStatus: "fetching"
          };
        case "fetch":
          return {
            ...state,
            ...fetchState(state.data, this.options),
            fetchMeta: action.meta ?? null
          };
        case "success":
          const newState = {
            ...state,
            ...successState(action.data, action.dataUpdatedAt),
            dataUpdateCount: state.dataUpdateCount + 1,
            ...!action.manual && {
              fetchStatus: "idle",
              fetchFailureCount: 0,
              fetchFailureReason: null
            }
          };
          this.#revertState = action.manual ? newState : void 0;
          return newState;
        case "error":
          const error = action.error;
          return {
            ...state,
            error,
            errorUpdateCount: state.errorUpdateCount + 1,
            errorUpdatedAt: Date.now(),
            fetchFailureCount: state.fetchFailureCount + 1,
            fetchFailureReason: error,
            fetchStatus: "idle",
            status: "error",
            // flag existing data as invalidated if we get a background error
            // note that "no data" always means stale so we can set unconditionally here
            isInvalidated: true
          };
        case "invalidate":
          return {
            ...state,
            isInvalidated: true
          };
        case "setState":
          return {
            ...state,
            ...action.state
          };
      }
    };
    this.state = reducer(this.state);
    notifyManager.batch(() => {
      this.observers.forEach((observer) => {
        observer.onQueryUpdate();
      });
      this.#cache.notify({ query: this, type: "updated", action });
    });
  }
};
function fetchState(data, options) {
  return {
    fetchFailureCount: 0,
    fetchFailureReason: null,
    fetchStatus: canFetch(options.networkMode) ? "fetching" : "paused",
    ...data === void 0 && {
      error: null,
      status: "pending"
    }
  };
}
function successState(data, dataUpdatedAt) {
  return {
    data,
    dataUpdatedAt: dataUpdatedAt ?? Date.now(),
    error: null,
    isInvalidated: false,
    status: "success"
  };
}
function getDefaultState$1(options) {
  const data = typeof options.initialData === "function" ? options.initialData() : options.initialData;
  const hasData = data !== void 0;
  const initialDataUpdatedAt = hasData ? typeof options.initialDataUpdatedAt === "function" ? options.initialDataUpdatedAt() : options.initialDataUpdatedAt : 0;
  return {
    data,
    dataUpdateCount: 0,
    dataUpdatedAt: hasData ? initialDataUpdatedAt ?? Date.now() : 0,
    error: null,
    errorUpdateCount: 0,
    errorUpdatedAt: 0,
    fetchFailureCount: 0,
    fetchFailureReason: null,
    fetchMeta: null,
    isInvalidated: false,
    status: hasData ? "success" : "pending",
    fetchStatus: "idle"
  };
}
var QueryObserver = class extends Subscribable {
  constructor(client, options) {
    super();
    this.options = options;
    this.#client = client;
    this.#selectError = null;
    this.#currentThenable = pendingThenable();
    this.bindMethods();
    this.setOptions(options);
  }
  #client;
  #currentQuery = void 0;
  #currentQueryInitialState = void 0;
  #currentResult = void 0;
  #currentResultState;
  #currentResultOptions;
  #currentThenable;
  #selectError;
  #selectFn;
  #selectResult;
  // This property keeps track of the last query with defined data.
  // It will be used to pass the previous data and query to the placeholder function between renders.
  #lastQueryWithDefinedData;
  #staleTimeoutId;
  #refetchIntervalId;
  #currentRefetchInterval;
  #trackedProps = /* @__PURE__ */ new Set();
  bindMethods() {
    this.refetch = this.refetch.bind(this);
  }
  onSubscribe() {
    if (this.listeners.size === 1) {
      this.#currentQuery.addObserver(this);
      if (shouldFetchOnMount(this.#currentQuery, this.options)) {
        this.#executeFetch();
      } else {
        this.updateResult();
      }
      this.#updateTimers();
    }
  }
  onUnsubscribe() {
    if (!this.hasListeners()) {
      this.destroy();
    }
  }
  shouldFetchOnReconnect() {
    return shouldFetchOn(
      this.#currentQuery,
      this.options,
      this.options.refetchOnReconnect
    );
  }
  shouldFetchOnWindowFocus() {
    return shouldFetchOn(
      this.#currentQuery,
      this.options,
      this.options.refetchOnWindowFocus
    );
  }
  destroy() {
    this.listeners = /* @__PURE__ */ new Set();
    this.#clearStaleTimeout();
    this.#clearRefetchInterval();
    this.#currentQuery.removeObserver(this);
  }
  setOptions(options) {
    const prevOptions = this.options;
    const prevQuery = this.#currentQuery;
    this.options = this.#client.defaultQueryOptions(options);
    if (this.options.enabled !== void 0 && typeof this.options.enabled !== "boolean" && typeof this.options.enabled !== "function" && typeof resolveQueryBoolean(this.options.enabled, this.#currentQuery) !== "boolean") {
      throw new Error(
        "Expected enabled to be a boolean or a callback that returns a boolean"
      );
    }
    this.#updateQuery();
    this.#currentQuery.setOptions(this.options);
    if (prevOptions._defaulted && !shallowEqualObjects(this.options, prevOptions)) {
      this.#client.getQueryCache().notify({
        type: "observerOptionsUpdated",
        query: this.#currentQuery,
        observer: this
      });
    }
    const mounted = this.hasListeners();
    if (mounted && shouldFetchOptionally(
      this.#currentQuery,
      prevQuery,
      this.options,
      prevOptions
    )) {
      this.#executeFetch();
    }
    this.updateResult();
    if (mounted && (this.#currentQuery !== prevQuery || resolveQueryBoolean(this.options.enabled, this.#currentQuery) !== resolveQueryBoolean(prevOptions.enabled, this.#currentQuery) || resolveStaleTime(this.options.staleTime, this.#currentQuery) !== resolveStaleTime(prevOptions.staleTime, this.#currentQuery))) {
      this.#updateStaleTimeout();
    }
    const nextRefetchInterval = this.#computeRefetchInterval();
    if (mounted && (this.#currentQuery !== prevQuery || resolveQueryBoolean(this.options.enabled, this.#currentQuery) !== resolveQueryBoolean(prevOptions.enabled, this.#currentQuery) || nextRefetchInterval !== this.#currentRefetchInterval)) {
      this.#updateRefetchInterval(nextRefetchInterval);
    }
  }
  getOptimisticResult(options) {
    const query = this.#client.getQueryCache().build(this.#client, options);
    const result = this.createResult(query, options);
    if (shouldAssignObserverCurrentProperties(this, result)) {
      this.#currentResult = result;
      this.#currentResultOptions = this.options;
      this.#currentResultState = this.#currentQuery.state;
    }
    return result;
  }
  getCurrentResult() {
    return this.#currentResult;
  }
  trackResult(result, onPropTracked) {
    return new Proxy(result, {
      get: (target, key) => {
        this.trackProp(key);
        onPropTracked?.(key);
        if (key === "promise") {
          this.trackProp("data");
          if (!this.options.experimental_prefetchInRender && this.#currentThenable.status === "pending") {
            this.#currentThenable.reject(
              new Error(
                "experimental_prefetchInRender feature flag is not enabled"
              )
            );
          }
        }
        return Reflect.get(target, key);
      }
    });
  }
  trackProp(key) {
    this.#trackedProps.add(key);
  }
  getCurrentQuery() {
    return this.#currentQuery;
  }
  refetch({ ...options } = {}) {
    return this.fetch({
      ...options
    });
  }
  fetchOptimistic(options) {
    const defaultedOptions = this.#client.defaultQueryOptions(options);
    const query = this.#client.getQueryCache().build(this.#client, defaultedOptions);
    return query.fetch().then(() => this.createResult(query, defaultedOptions));
  }
  fetch(fetchOptions) {
    return this.#executeFetch({
      ...fetchOptions,
      cancelRefetch: fetchOptions.cancelRefetch ?? true
    }).then(() => {
      this.updateResult();
      return this.#currentResult;
    });
  }
  #executeFetch(fetchOptions) {
    this.#updateQuery();
    let promise = this.#currentQuery.fetch(
      this.options,
      fetchOptions
    );
    if (!fetchOptions?.throwOnError) {
      promise = promise.catch(noop$1);
    }
    return promise;
  }
  #updateStaleTimeout() {
    this.#clearStaleTimeout();
    const staleTime = resolveStaleTime(
      this.options.staleTime,
      this.#currentQuery
    );
    if (environmentManager.isServer() || this.#currentResult.isStale || !isValidTimeout(staleTime)) {
      return;
    }
    const time = timeUntilStale(this.#currentResult.dataUpdatedAt, staleTime);
    const timeout = time + 1;
    this.#staleTimeoutId = timeoutManager.setTimeout(() => {
      if (!this.#currentResult.isStale) {
        this.updateResult();
      }
    }, timeout);
  }
  #computeRefetchInterval() {
    return (typeof this.options.refetchInterval === "function" ? this.options.refetchInterval(this.#currentQuery) : this.options.refetchInterval) ?? false;
  }
  #updateRefetchInterval(nextInterval) {
    this.#clearRefetchInterval();
    this.#currentRefetchInterval = nextInterval;
    if (environmentManager.isServer() || resolveQueryBoolean(this.options.enabled, this.#currentQuery) === false || !isValidTimeout(this.#currentRefetchInterval) || this.#currentRefetchInterval === 0) {
      return;
    }
    this.#refetchIntervalId = timeoutManager.setInterval(() => {
      if (this.options.refetchIntervalInBackground || focusManager.isFocused()) {
        this.#executeFetch();
      }
    }, this.#currentRefetchInterval);
  }
  #updateTimers() {
    this.#updateStaleTimeout();
    this.#updateRefetchInterval(this.#computeRefetchInterval());
  }
  #clearStaleTimeout() {
    if (this.#staleTimeoutId !== void 0) {
      timeoutManager.clearTimeout(this.#staleTimeoutId);
      this.#staleTimeoutId = void 0;
    }
  }
  #clearRefetchInterval() {
    if (this.#refetchIntervalId !== void 0) {
      timeoutManager.clearInterval(this.#refetchIntervalId);
      this.#refetchIntervalId = void 0;
    }
  }
  createResult(query, options) {
    const prevQuery = this.#currentQuery;
    const prevOptions = this.options;
    const prevResult = this.#currentResult;
    const prevResultState = this.#currentResultState;
    const prevResultOptions = this.#currentResultOptions;
    const queryChange = query !== prevQuery;
    const queryInitialState = queryChange ? query.state : this.#currentQueryInitialState;
    const { state } = query;
    let newState = { ...state };
    let isPlaceholderData = false;
    let data;
    if (options._optimisticResults) {
      const mounted = this.hasListeners();
      const fetchOnMount = !mounted && shouldFetchOnMount(query, options);
      const fetchOptionally = mounted && shouldFetchOptionally(query, prevQuery, options, prevOptions);
      if (fetchOnMount || fetchOptionally) {
        newState = {
          ...newState,
          ...fetchState(state.data, query.options)
        };
      }
      if (options._optimisticResults === "isRestoring") {
        newState.fetchStatus = "idle";
      }
    }
    let { error, errorUpdatedAt, status } = newState;
    data = newState.data;
    let skipSelect = false;
    if (options.placeholderData !== void 0 && data === void 0 && status === "pending") {
      let placeholderData;
      if (prevResult?.isPlaceholderData && options.placeholderData === prevResultOptions?.placeholderData) {
        placeholderData = prevResult.data;
        skipSelect = true;
      } else {
        placeholderData = typeof options.placeholderData === "function" ? options.placeholderData(
          this.#lastQueryWithDefinedData?.state.data,
          this.#lastQueryWithDefinedData
        ) : options.placeholderData;
      }
      if (placeholderData !== void 0) {
        status = "success";
        data = replaceData(
          prevResult?.data,
          placeholderData,
          options
        );
        isPlaceholderData = true;
      }
    }
    if (options.select && data !== void 0 && !skipSelect) {
      if (prevResult && data === prevResultState?.data && options.select === this.#selectFn) {
        data = this.#selectResult;
      } else {
        try {
          this.#selectFn = options.select;
          data = options.select(data);
          data = replaceData(prevResult?.data, data, options);
          this.#selectResult = data;
          this.#selectError = null;
        } catch (selectError) {
          this.#selectError = selectError;
        }
      }
    }
    if (this.#selectError) {
      error = this.#selectError;
      data = this.#selectResult;
      errorUpdatedAt = Date.now();
      status = "error";
    }
    const isFetching = newState.fetchStatus === "fetching";
    const isPending = status === "pending";
    const isError = status === "error";
    const isLoading = isPending && isFetching;
    const hasData = data !== void 0;
    const result = {
      status,
      fetchStatus: newState.fetchStatus,
      isPending,
      isSuccess: status === "success",
      isError,
      isInitialLoading: isLoading,
      isLoading,
      data,
      dataUpdatedAt: newState.dataUpdatedAt,
      error,
      errorUpdatedAt,
      failureCount: newState.fetchFailureCount,
      failureReason: newState.fetchFailureReason,
      errorUpdateCount: newState.errorUpdateCount,
      isFetched: query.isFetched(),
      isFetchedAfterMount: newState.dataUpdateCount > queryInitialState.dataUpdateCount || newState.errorUpdateCount > queryInitialState.errorUpdateCount,
      isFetching,
      isRefetching: isFetching && !isPending,
      isLoadingError: isError && !hasData,
      isPaused: newState.fetchStatus === "paused",
      isPlaceholderData,
      isRefetchError: isError && hasData,
      isStale: isStale(query, options),
      refetch: this.refetch,
      promise: this.#currentThenable,
      isEnabled: resolveQueryBoolean(options.enabled, query) !== false
    };
    const nextResult = result;
    if (this.options.experimental_prefetchInRender) {
      const hasResultData = nextResult.data !== void 0;
      const isErrorWithoutData = nextResult.status === "error" && !hasResultData;
      const finalizeThenableIfPossible = (thenable) => {
        if (isErrorWithoutData) {
          thenable.reject(nextResult.error);
        } else if (hasResultData) {
          thenable.resolve(nextResult.data);
        }
      };
      const recreateThenable = () => {
        const pending = this.#currentThenable = nextResult.promise = pendingThenable();
        finalizeThenableIfPossible(pending);
      };
      const prevThenable = this.#currentThenable;
      switch (prevThenable.status) {
        case "pending":
          if (query.queryHash === prevQuery.queryHash) {
            finalizeThenableIfPossible(prevThenable);
          }
          break;
        case "fulfilled":
          if (isErrorWithoutData || nextResult.data !== prevThenable.value) {
            recreateThenable();
          }
          break;
        case "rejected":
          if (!isErrorWithoutData || nextResult.error !== prevThenable.reason) {
            recreateThenable();
          }
          break;
      }
    }
    return nextResult;
  }
  updateResult() {
    const prevResult = this.#currentResult;
    const nextResult = this.createResult(this.#currentQuery, this.options);
    this.#currentResultState = this.#currentQuery.state;
    this.#currentResultOptions = this.options;
    if (this.#currentResultState.data !== void 0) {
      this.#lastQueryWithDefinedData = this.#currentQuery;
    }
    if (shallowEqualObjects(nextResult, prevResult)) {
      return;
    }
    this.#currentResult = nextResult;
    const shouldNotifyListeners = () => {
      if (!prevResult) {
        return true;
      }
      const { notifyOnChangeProps } = this.options;
      const notifyOnChangePropsValue = typeof notifyOnChangeProps === "function" ? notifyOnChangeProps() : notifyOnChangeProps;
      if (notifyOnChangePropsValue === "all" || !notifyOnChangePropsValue && !this.#trackedProps.size) {
        return true;
      }
      const includedProps = new Set(
        notifyOnChangePropsValue ?? this.#trackedProps
      );
      if (this.options.throwOnError) {
        includedProps.add("error");
      }
      return Object.keys(this.#currentResult).some((key) => {
        const typedKey = key;
        const changed = this.#currentResult[typedKey] !== prevResult[typedKey];
        return changed && includedProps.has(typedKey);
      });
    };
    this.#notify({ listeners: shouldNotifyListeners() });
  }
  #updateQuery() {
    const query = this.#client.getQueryCache().build(this.#client, this.options);
    if (query === this.#currentQuery) {
      return;
    }
    const prevQuery = this.#currentQuery;
    this.#currentQuery = query;
    this.#currentQueryInitialState = query.state;
    if (this.hasListeners()) {
      prevQuery?.removeObserver(this);
      query.addObserver(this);
    }
  }
  onQueryUpdate() {
    this.updateResult();
    if (this.hasListeners()) {
      this.#updateTimers();
    }
  }
  #notify(notifyOptions) {
    notifyManager.batch(() => {
      if (notifyOptions.listeners) {
        this.listeners.forEach((listener) => {
          listener(this.#currentResult);
        });
      }
      this.#client.getQueryCache().notify({
        query: this.#currentQuery,
        type: "observerResultsUpdated"
      });
    });
  }
};
function shouldLoadOnMount(query, options) {
  return resolveQueryBoolean(options.enabled, query) !== false && query.state.data === void 0 && !(query.state.status === "error" && resolveQueryBoolean(options.retryOnMount, query) === false);
}
function shouldFetchOnMount(query, options) {
  return shouldLoadOnMount(query, options) || query.state.data !== void 0 && shouldFetchOn(query, options, options.refetchOnMount);
}
function shouldFetchOn(query, options, field) {
  if (resolveQueryBoolean(options.enabled, query) !== false && resolveStaleTime(options.staleTime, query) !== "static") {
    const value = typeof field === "function" ? field(query) : field;
    return value === "always" || value !== false && isStale(query, options);
  }
  return false;
}
function shouldFetchOptionally(query, prevQuery, options, prevOptions) {
  return (query !== prevQuery || resolveQueryBoolean(prevOptions.enabled, query) === false) && (!options.suspense || query.state.status !== "error") && isStale(query, options);
}
function isStale(query, options) {
  return resolveQueryBoolean(options.enabled, query) !== false && query.isStaleByTime(resolveStaleTime(options.staleTime, query));
}
function shouldAssignObserverCurrentProperties(observer, optimisticResult) {
  if (!shallowEqualObjects(observer.getCurrentResult(), optimisticResult)) {
    return true;
  }
  return false;
}
function infiniteQueryBehavior(pages) {
  return {
    onFetch: (context, query) => {
      const options = context.options;
      const direction = context.fetchOptions?.meta?.fetchMore?.direction;
      const oldPages = context.state.data?.pages || [];
      const oldPageParams = context.state.data?.pageParams || [];
      let result = { pages: [], pageParams: [] };
      let currentPage = 0;
      const fetchFn = async () => {
        let cancelled = false;
        const addSignalProperty = (object) => {
          addConsumeAwareSignal(
            object,
            () => context.signal,
            () => cancelled = true
          );
        };
        const queryFn = ensureQueryFn(context.options, context.fetchOptions);
        const fetchPage = async (data, param, previous) => {
          if (cancelled) {
            return Promise.reject();
          }
          if (param == null && data.pages.length) {
            return Promise.resolve(data);
          }
          const createQueryFnContext = () => {
            const queryFnContext2 = {
              client: context.client,
              queryKey: context.queryKey,
              pageParam: param,
              direction: previous ? "backward" : "forward",
              meta: context.options.meta
            };
            addSignalProperty(queryFnContext2);
            return queryFnContext2;
          };
          const queryFnContext = createQueryFnContext();
          const page = await queryFn(queryFnContext);
          const { maxPages } = context.options;
          const addTo = previous ? addToStart : addToEnd;
          return {
            pages: addTo(data.pages, page, maxPages),
            pageParams: addTo(data.pageParams, param, maxPages)
          };
        };
        if (direction && oldPages.length) {
          const previous = direction === "backward";
          const pageParamFn = previous ? getPreviousPageParam : getNextPageParam;
          const oldData = {
            pages: oldPages,
            pageParams: oldPageParams
          };
          const param = pageParamFn(options, oldData);
          result = await fetchPage(oldData, param, previous);
        } else {
          const remainingPages = pages ?? oldPages.length;
          do {
            const param = currentPage === 0 ? oldPageParams[0] ?? options.initialPageParam : getNextPageParam(options, result);
            if (currentPage > 0 && param == null) {
              break;
            }
            result = await fetchPage(result, param);
            currentPage++;
          } while (currentPage < remainingPages);
        }
        return result;
      };
      if (context.options.persister) {
        context.fetchFn = () => {
          return context.options.persister?.(
            fetchFn,
            {
              client: context.client,
              queryKey: context.queryKey,
              meta: context.options.meta,
              signal: context.signal
            },
            query
          );
        };
      } else {
        context.fetchFn = fetchFn;
      }
    }
  };
}
function getNextPageParam(options, { pages, pageParams }) {
  const lastIndex = pages.length - 1;
  return pages.length > 0 ? options.getNextPageParam(
    pages[lastIndex],
    pages,
    pageParams[lastIndex],
    pageParams
  ) : void 0;
}
function getPreviousPageParam(options, { pages, pageParams }) {
  return pages.length > 0 ? options.getPreviousPageParam?.(pages[0], pages, pageParams[0], pageParams) : void 0;
}
var Mutation = class extends Removable {
  #client;
  #observers;
  #mutationCache;
  #retryer;
  constructor(config) {
    super();
    this.#client = config.client;
    this.mutationId = config.mutationId;
    this.#mutationCache = config.mutationCache;
    this.#observers = [];
    this.state = config.state || getDefaultState();
    this.setOptions(config.options);
    this.scheduleGc();
  }
  setOptions(options) {
    this.options = options;
    this.updateGcTime(this.options.gcTime);
  }
  get meta() {
    return this.options.meta;
  }
  addObserver(observer) {
    if (!this.#observers.includes(observer)) {
      this.#observers.push(observer);
      this.clearGcTimeout();
      this.#mutationCache.notify({
        type: "observerAdded",
        mutation: this,
        observer
      });
    }
  }
  removeObserver(observer) {
    this.#observers = this.#observers.filter((x) => x !== observer);
    this.scheduleGc();
    this.#mutationCache.notify({
      type: "observerRemoved",
      mutation: this,
      observer
    });
  }
  optionalRemove() {
    if (!this.#observers.length) {
      if (this.state.status === "pending") {
        this.scheduleGc();
      } else {
        this.#mutationCache.remove(this);
      }
    }
  }
  continue() {
    return this.#retryer?.continue() ?? // continuing a mutation assumes that variables are set, mutation must have been dehydrated before
    this.execute(this.state.variables);
  }
  async execute(variables) {
    const onContinue = () => {
      this.#dispatch({ type: "continue" });
    };
    const mutationFnContext = {
      client: this.#client,
      meta: this.options.meta,
      mutationKey: this.options.mutationKey
    };
    this.#retryer = createRetryer({
      fn: () => {
        if (!this.options.mutationFn) {
          return Promise.reject(new Error("No mutationFn found"));
        }
        return this.options.mutationFn(variables, mutationFnContext);
      },
      onFail: (failureCount, error) => {
        this.#dispatch({ type: "failed", failureCount, error });
      },
      onPause: () => {
        this.#dispatch({ type: "pause" });
      },
      onContinue,
      retry: this.options.retry ?? 0,
      retryDelay: this.options.retryDelay,
      networkMode: this.options.networkMode,
      canRun: () => this.#mutationCache.canRun(this)
    });
    const restored = this.state.status === "pending";
    const isPaused = !this.#retryer.canStart();
    try {
      if (restored) {
        onContinue();
      } else {
        this.#dispatch({ type: "pending", variables, isPaused });
        if (this.#mutationCache.config.onMutate) {
          await this.#mutationCache.config.onMutate(
            variables,
            this,
            mutationFnContext
          );
        }
        const context = await this.options.onMutate?.(
          variables,
          mutationFnContext
        );
        if (context !== this.state.context) {
          this.#dispatch({
            type: "pending",
            context,
            variables,
            isPaused
          });
        }
      }
      const data = await this.#retryer.start();
      await this.#mutationCache.config.onSuccess?.(
        data,
        variables,
        this.state.context,
        this,
        mutationFnContext
      );
      await this.options.onSuccess?.(
        data,
        variables,
        this.state.context,
        mutationFnContext
      );
      await this.#mutationCache.config.onSettled?.(
        data,
        null,
        this.state.variables,
        this.state.context,
        this,
        mutationFnContext
      );
      await this.options.onSettled?.(
        data,
        null,
        variables,
        this.state.context,
        mutationFnContext
      );
      this.#dispatch({ type: "success", data });
      return data;
    } catch (error) {
      try {
        await this.#mutationCache.config.onError?.(
          error,
          variables,
          this.state.context,
          this,
          mutationFnContext
        );
      } catch (e) {
        void Promise.reject(e);
      }
      try {
        await this.options.onError?.(
          error,
          variables,
          this.state.context,
          mutationFnContext
        );
      } catch (e) {
        void Promise.reject(e);
      }
      try {
        await this.#mutationCache.config.onSettled?.(
          void 0,
          error,
          this.state.variables,
          this.state.context,
          this,
          mutationFnContext
        );
      } catch (e) {
        void Promise.reject(e);
      }
      try {
        await this.options.onSettled?.(
          void 0,
          error,
          variables,
          this.state.context,
          mutationFnContext
        );
      } catch (e) {
        void Promise.reject(e);
      }
      this.#dispatch({ type: "error", error });
      throw error;
    } finally {
      this.#mutationCache.runNext(this);
    }
  }
  #dispatch(action) {
    const reducer = (state) => {
      switch (action.type) {
        case "failed":
          return {
            ...state,
            failureCount: action.failureCount,
            failureReason: action.error
          };
        case "pause":
          return {
            ...state,
            isPaused: true
          };
        case "continue":
          return {
            ...state,
            isPaused: false
          };
        case "pending":
          return {
            ...state,
            context: action.context,
            data: void 0,
            failureCount: 0,
            failureReason: null,
            error: null,
            isPaused: action.isPaused,
            status: "pending",
            variables: action.variables,
            submittedAt: Date.now()
          };
        case "success":
          return {
            ...state,
            data: action.data,
            failureCount: 0,
            failureReason: null,
            error: null,
            status: "success",
            isPaused: false
          };
        case "error":
          return {
            ...state,
            data: void 0,
            error: action.error,
            failureCount: state.failureCount + 1,
            failureReason: action.error,
            isPaused: false,
            status: "error"
          };
      }
    };
    this.state = reducer(this.state);
    notifyManager.batch(() => {
      this.#observers.forEach((observer) => {
        observer.onMutationUpdate(action);
      });
      this.#mutationCache.notify({
        mutation: this,
        type: "updated",
        action
      });
    });
  }
};
function getDefaultState() {
  return {
    context: void 0,
    data: void 0,
    error: null,
    failureCount: 0,
    failureReason: null,
    isPaused: false,
    status: "idle",
    variables: void 0,
    submittedAt: 0
  };
}
var MutationCache = class extends Subscribable {
  constructor(config = {}) {
    super();
    this.config = config;
    this.#mutations = /* @__PURE__ */ new Set();
    this.#scopes = /* @__PURE__ */ new Map();
    this.#mutationId = 0;
  }
  #mutations;
  #scopes;
  #mutationId;
  build(client, options, state) {
    const mutation = new Mutation({
      client,
      mutationCache: this,
      mutationId: ++this.#mutationId,
      options: client.defaultMutationOptions(options),
      state
    });
    this.add(mutation);
    return mutation;
  }
  add(mutation) {
    this.#mutations.add(mutation);
    const scope = scopeFor(mutation);
    if (typeof scope === "string") {
      const scopedMutations = this.#scopes.get(scope);
      if (scopedMutations) {
        scopedMutations.push(mutation);
      } else {
        this.#scopes.set(scope, [mutation]);
      }
    }
    this.notify({ type: "added", mutation });
  }
  remove(mutation) {
    if (this.#mutations.delete(mutation)) {
      const scope = scopeFor(mutation);
      if (typeof scope === "string") {
        const scopedMutations = this.#scopes.get(scope);
        if (scopedMutations) {
          if (scopedMutations.length > 1) {
            const index = scopedMutations.indexOf(mutation);
            if (index !== -1) {
              scopedMutations.splice(index, 1);
            }
          } else if (scopedMutations[0] === mutation) {
            this.#scopes.delete(scope);
          }
        }
      }
    }
    this.notify({ type: "removed", mutation });
  }
  canRun(mutation) {
    const scope = scopeFor(mutation);
    if (typeof scope === "string") {
      const mutationsWithSameScope = this.#scopes.get(scope);
      const firstPendingMutation = mutationsWithSameScope?.find(
        (m) => m.state.status === "pending"
      );
      return !firstPendingMutation || firstPendingMutation === mutation;
    } else {
      return true;
    }
  }
  runNext(mutation) {
    const scope = scopeFor(mutation);
    if (typeof scope === "string") {
      const foundMutation = this.#scopes.get(scope)?.find((m) => m !== mutation && m.state.isPaused);
      return foundMutation?.continue() ?? Promise.resolve();
    } else {
      return Promise.resolve();
    }
  }
  clear() {
    notifyManager.batch(() => {
      this.#mutations.forEach((mutation) => {
        this.notify({ type: "removed", mutation });
      });
      this.#mutations.clear();
      this.#scopes.clear();
    });
  }
  getAll() {
    return Array.from(this.#mutations);
  }
  find(filters) {
    const defaultedFilters = { exact: true, ...filters };
    return this.getAll().find(
      (mutation) => matchMutation(defaultedFilters, mutation)
    );
  }
  findAll(filters = {}) {
    return this.getAll().filter((mutation) => matchMutation(filters, mutation));
  }
  notify(event) {
    notifyManager.batch(() => {
      this.listeners.forEach((listener) => {
        listener(event);
      });
    });
  }
  resumePausedMutations() {
    const pausedMutations = this.getAll().filter((x) => x.state.isPaused);
    return notifyManager.batch(
      () => Promise.all(
        pausedMutations.map((mutation) => mutation.continue().catch(noop$1))
      )
    );
  }
};
function scopeFor(mutation) {
  return mutation.options.scope?.id;
}
var QueryCache = class extends Subscribable {
  constructor(config = {}) {
    super();
    this.config = config;
    this.#queries = /* @__PURE__ */ new Map();
  }
  #queries;
  build(client, options, state) {
    const queryKey = options.queryKey;
    const queryHash = options.queryHash ?? hashQueryKeyByOptions(queryKey, options);
    let query = this.get(queryHash);
    if (!query) {
      query = new Query({
        client,
        queryKey,
        queryHash,
        options: client.defaultQueryOptions(options),
        state,
        defaultOptions: client.getQueryDefaults(queryKey)
      });
      this.add(query);
    }
    return query;
  }
  add(query) {
    if (!this.#queries.has(query.queryHash)) {
      this.#queries.set(query.queryHash, query);
      this.notify({
        type: "added",
        query
      });
    }
  }
  remove(query) {
    const queryInMap = this.#queries.get(query.queryHash);
    if (queryInMap) {
      query.destroy();
      if (queryInMap === query) {
        this.#queries.delete(query.queryHash);
      }
      this.notify({ type: "removed", query });
    }
  }
  clear() {
    notifyManager.batch(() => {
      this.getAll().forEach((query) => {
        this.remove(query);
      });
    });
  }
  get(queryHash) {
    return this.#queries.get(queryHash);
  }
  getAll() {
    return [...this.#queries.values()];
  }
  find(filters) {
    const defaultedFilters = { exact: true, ...filters };
    return this.getAll().find(
      (query) => matchQuery(defaultedFilters, query)
    );
  }
  findAll(filters = {}) {
    const queries = this.getAll();
    return Object.keys(filters).length > 0 ? queries.filter((query) => matchQuery(filters, query)) : queries;
  }
  notify(event) {
    notifyManager.batch(() => {
      this.listeners.forEach((listener) => {
        listener(event);
      });
    });
  }
  onFocus() {
    notifyManager.batch(() => {
      this.getAll().forEach((query) => {
        query.onFocus();
      });
    });
  }
  onOnline() {
    notifyManager.batch(() => {
      this.getAll().forEach((query) => {
        query.onOnline();
      });
    });
  }
};
var QueryClient = class {
  #queryCache;
  #mutationCache;
  #defaultOptions;
  #queryDefaults;
  #mutationDefaults;
  #mountCount;
  #unsubscribeFocus;
  #unsubscribeOnline;
  constructor(config = {}) {
    this.#queryCache = config.queryCache || new QueryCache();
    this.#mutationCache = config.mutationCache || new MutationCache();
    this.#defaultOptions = config.defaultOptions || {};
    this.#queryDefaults = /* @__PURE__ */ new Map();
    this.#mutationDefaults = /* @__PURE__ */ new Map();
    this.#mountCount = 0;
  }
  mount() {
    this.#mountCount++;
    if (this.#mountCount !== 1) return;
    this.#unsubscribeFocus = focusManager.subscribe(async (focused) => {
      if (focused) {
        await this.resumePausedMutations();
        this.#queryCache.onFocus();
      }
    });
    this.#unsubscribeOnline = onlineManager.subscribe(async (online) => {
      if (online) {
        await this.resumePausedMutations();
        this.#queryCache.onOnline();
      }
    });
  }
  unmount() {
    this.#mountCount--;
    if (this.#mountCount !== 0) return;
    this.#unsubscribeFocus?.();
    this.#unsubscribeFocus = void 0;
    this.#unsubscribeOnline?.();
    this.#unsubscribeOnline = void 0;
  }
  isFetching(filters) {
    return this.#queryCache.findAll({ ...filters, fetchStatus: "fetching" }).length;
  }
  isMutating(filters) {
    return this.#mutationCache.findAll({ ...filters, status: "pending" }).length;
  }
  /**
   * Imperative (non-reactive) way to retrieve data for a QueryKey.
   * Should only be used in callbacks or functions where reading the latest data is necessary, e.g. for optimistic updates.
   *
   * Hint: Do not use this function inside a component, because it won't receive updates.
   * Use `useQuery` to create a `QueryObserver` that subscribes to changes.
   */
  getQueryData(queryKey) {
    const options = this.defaultQueryOptions({ queryKey });
    return this.#queryCache.get(options.queryHash)?.state.data;
  }
  ensureQueryData(options) {
    const defaultedOptions = this.defaultQueryOptions(options);
    const query = this.#queryCache.build(this, defaultedOptions);
    const cachedData = query.state.data;
    if (cachedData === void 0) {
      return this.fetchQuery(options);
    }
    if (options.revalidateIfStale && query.isStaleByTime(resolveStaleTime(defaultedOptions.staleTime, query))) {
      void this.prefetchQuery(defaultedOptions);
    }
    return Promise.resolve(cachedData);
  }
  getQueriesData(filters) {
    return this.#queryCache.findAll(filters).map(({ queryKey, state }) => {
      const data = state.data;
      return [queryKey, data];
    });
  }
  setQueryData(queryKey, updater, options) {
    const defaultedOptions = this.defaultQueryOptions({ queryKey });
    const query = this.#queryCache.get(
      defaultedOptions.queryHash
    );
    const prevData = query?.state.data;
    const data = functionalUpdate(updater, prevData);
    if (data === void 0) {
      return void 0;
    }
    return this.#queryCache.build(this, defaultedOptions).setData(data, { ...options, manual: true });
  }
  setQueriesData(filters, updater, options) {
    return notifyManager.batch(
      () => this.#queryCache.findAll(filters).map(({ queryKey }) => [
        queryKey,
        this.setQueryData(queryKey, updater, options)
      ])
    );
  }
  getQueryState(queryKey) {
    const options = this.defaultQueryOptions({ queryKey });
    return this.#queryCache.get(
      options.queryHash
    )?.state;
  }
  removeQueries(filters) {
    const queryCache = this.#queryCache;
    notifyManager.batch(() => {
      queryCache.findAll(filters).forEach((query) => {
        queryCache.remove(query);
      });
    });
  }
  resetQueries(filters, options) {
    const queryCache = this.#queryCache;
    return notifyManager.batch(() => {
      queryCache.findAll(filters).forEach((query) => {
        query.reset();
      });
      return this.refetchQueries(
        {
          type: "active",
          ...filters
        },
        options
      );
    });
  }
  cancelQueries(filters, cancelOptions = {}) {
    const defaultedCancelOptions = { revert: true, ...cancelOptions };
    const promises = notifyManager.batch(
      () => this.#queryCache.findAll(filters).map((query) => query.cancel(defaultedCancelOptions))
    );
    return Promise.all(promises).then(noop$1).catch(noop$1);
  }
  invalidateQueries(filters, options = {}) {
    return notifyManager.batch(() => {
      this.#queryCache.findAll(filters).forEach((query) => {
        query.invalidate();
      });
      if (filters?.refetchType === "none") {
        return Promise.resolve();
      }
      return this.refetchQueries(
        {
          ...filters,
          type: filters?.refetchType ?? filters?.type ?? "active"
        },
        options
      );
    });
  }
  refetchQueries(filters, options = {}) {
    const fetchOptions = {
      ...options,
      cancelRefetch: options.cancelRefetch ?? true
    };
    const promises = notifyManager.batch(
      () => this.#queryCache.findAll(filters).filter((query) => !query.isDisabled() && !query.isStatic()).map((query) => {
        let promise = query.fetch(void 0, fetchOptions);
        if (!fetchOptions.throwOnError) {
          promise = promise.catch(noop$1);
        }
        return query.state.fetchStatus === "paused" ? Promise.resolve() : promise;
      })
    );
    return Promise.all(promises).then(noop$1);
  }
  fetchQuery(options) {
    const defaultedOptions = this.defaultQueryOptions(options);
    if (defaultedOptions.retry === void 0) {
      defaultedOptions.retry = false;
    }
    const query = this.#queryCache.build(this, defaultedOptions);
    return query.isStaleByTime(
      resolveStaleTime(defaultedOptions.staleTime, query)
    ) ? query.fetch(defaultedOptions) : Promise.resolve(query.state.data);
  }
  prefetchQuery(options) {
    return this.fetchQuery(options).then(noop$1).catch(noop$1);
  }
  fetchInfiniteQuery(options) {
    options.behavior = infiniteQueryBehavior(options.pages);
    return this.fetchQuery(options);
  }
  prefetchInfiniteQuery(options) {
    return this.fetchInfiniteQuery(options).then(noop$1).catch(noop$1);
  }
  ensureInfiniteQueryData(options) {
    options.behavior = infiniteQueryBehavior(options.pages);
    return this.ensureQueryData(options);
  }
  resumePausedMutations() {
    if (onlineManager.isOnline()) {
      return this.#mutationCache.resumePausedMutations();
    }
    return Promise.resolve();
  }
  getQueryCache() {
    return this.#queryCache;
  }
  getMutationCache() {
    return this.#mutationCache;
  }
  getDefaultOptions() {
    return this.#defaultOptions;
  }
  setDefaultOptions(options) {
    this.#defaultOptions = options;
  }
  setQueryDefaults(queryKey, options) {
    this.#queryDefaults.set(hashKey(queryKey), {
      queryKey,
      defaultOptions: options
    });
  }
  getQueryDefaults(queryKey) {
    const defaults2 = [...this.#queryDefaults.values()];
    const result = {};
    defaults2.forEach((queryDefault) => {
      if (partialMatchKey(queryKey, queryDefault.queryKey)) {
        Object.assign(result, queryDefault.defaultOptions);
      }
    });
    return result;
  }
  setMutationDefaults(mutationKey, options) {
    this.#mutationDefaults.set(hashKey(mutationKey), {
      mutationKey,
      defaultOptions: options
    });
  }
  getMutationDefaults(mutationKey) {
    const defaults2 = [...this.#mutationDefaults.values()];
    const result = {};
    defaults2.forEach((queryDefault) => {
      if (partialMatchKey(mutationKey, queryDefault.mutationKey)) {
        Object.assign(result, queryDefault.defaultOptions);
      }
    });
    return result;
  }
  defaultQueryOptions(options) {
    if (options._defaulted) {
      return options;
    }
    const defaultedOptions = {
      ...this.#defaultOptions.queries,
      ...this.getQueryDefaults(options.queryKey),
      ...options,
      _defaulted: true
    };
    if (!defaultedOptions.queryHash) {
      defaultedOptions.queryHash = hashQueryKeyByOptions(
        defaultedOptions.queryKey,
        defaultedOptions
      );
    }
    if (defaultedOptions.refetchOnReconnect === void 0) {
      defaultedOptions.refetchOnReconnect = defaultedOptions.networkMode !== "always";
    }
    if (defaultedOptions.throwOnError === void 0) {
      defaultedOptions.throwOnError = !!defaultedOptions.suspense;
    }
    if (!defaultedOptions.networkMode && defaultedOptions.persister) {
      defaultedOptions.networkMode = "offlineFirst";
    }
    if (defaultedOptions.queryFn === skipToken) {
      defaultedOptions.enabled = false;
    }
    return defaultedOptions;
  }
  defaultMutationOptions(options) {
    if (options?._defaulted) {
      return options;
    }
    return {
      ...this.#defaultOptions.mutations,
      ...options?.mutationKey && this.getMutationDefaults(options.mutationKey),
      ...options,
      _defaulted: true
    };
  }
  clear() {
    this.#queryCache.clear();
    this.#mutationCache.clear();
  }
};
var QueryClientContext = reactExports.createContext(
  void 0
);
var useQueryClient = (queryClient) => {
  const client = reactExports.useContext(QueryClientContext);
  if (!client) {
    throw new Error("No QueryClient set, use QueryClientProvider to set one");
  }
  return client;
};
var QueryClientProvider = ({
  client,
  children
}) => {
  reactExports.useEffect(() => {
    client.mount();
    return () => {
      client.unmount();
    };
  }, [client]);
  return /* @__PURE__ */ jsxRuntimeExports.jsx(QueryClientContext.Provider, { value: client, children });
};
var IsRestoringContext = reactExports.createContext(false);
var useIsRestoring = () => reactExports.useContext(IsRestoringContext);
IsRestoringContext.Provider;
function createValue() {
  let isReset = false;
  return {
    clearReset: () => {
      isReset = false;
    },
    reset: () => {
      isReset = true;
    },
    isReset: () => {
      return isReset;
    }
  };
}
var QueryErrorResetBoundaryContext = reactExports.createContext(createValue());
var useQueryErrorResetBoundary = () => reactExports.useContext(QueryErrorResetBoundaryContext);
var ensurePreventErrorBoundaryRetry = (options, errorResetBoundary, query) => {
  const throwOnError = query?.state.error && typeof options.throwOnError === "function" ? shouldThrowError(options.throwOnError, [query.state.error, query]) : options.throwOnError;
  if (options.suspense || options.experimental_prefetchInRender || throwOnError) {
    if (!errorResetBoundary.isReset()) {
      options.retryOnMount = false;
    }
  }
};
var useClearResetErrorBoundary = (errorResetBoundary) => {
  reactExports.useEffect(() => {
    errorResetBoundary.clearReset();
  }, [errorResetBoundary]);
};
var getHasError = ({
  result,
  errorResetBoundary,
  throwOnError,
  query,
  suspense
}) => {
  return result.isError && !errorResetBoundary.isReset() && !result.isFetching && query && (suspense && result.data === void 0 || shouldThrowError(throwOnError, [result.error, query]));
};
var ensureSuspenseTimers = (defaultedOptions) => {
  if (defaultedOptions.suspense) {
    const MIN_SUSPENSE_TIME_MS = 1e3;
    const clamp = (value) => value === "static" ? value : Math.max(value ?? MIN_SUSPENSE_TIME_MS, MIN_SUSPENSE_TIME_MS);
    const originalStaleTime = defaultedOptions.staleTime;
    defaultedOptions.staleTime = typeof originalStaleTime === "function" ? (...args) => clamp(originalStaleTime(...args)) : clamp(originalStaleTime);
    if (typeof defaultedOptions.gcTime === "number") {
      defaultedOptions.gcTime = Math.max(
        defaultedOptions.gcTime,
        MIN_SUSPENSE_TIME_MS
      );
    }
  }
};
var willFetch = (result, isRestoring) => result.isLoading && result.isFetching && !isRestoring;
var shouldSuspend = (defaultedOptions, result) => defaultedOptions?.suspense && result.isPending;
var fetchOptimistic = (defaultedOptions, observer, errorResetBoundary) => observer.fetchOptimistic(defaultedOptions).catch(() => {
  errorResetBoundary.clearReset();
});
function useBaseQuery(options, Observer, queryClient) {
  const isRestoring = useIsRestoring();
  const errorResetBoundary = useQueryErrorResetBoundary();
  const client = useQueryClient();
  const defaultedOptions = client.defaultQueryOptions(options);
  client.getDefaultOptions().queries?._experimental_beforeQuery?.(
    defaultedOptions
  );
  const query = client.getQueryCache().get(defaultedOptions.queryHash);
  defaultedOptions._optimisticResults = isRestoring ? "isRestoring" : "optimistic";
  ensureSuspenseTimers(defaultedOptions);
  ensurePreventErrorBoundaryRetry(defaultedOptions, errorResetBoundary, query);
  useClearResetErrorBoundary(errorResetBoundary);
  const isNewCacheEntry = !client.getQueryCache().get(defaultedOptions.queryHash);
  const [observer] = reactExports.useState(
    () => new Observer(
      client,
      defaultedOptions
    )
  );
  const result = observer.getOptimisticResult(defaultedOptions);
  const shouldSubscribe = !isRestoring && options.subscribed !== false;
  reactExports.useSyncExternalStore(
    reactExports.useCallback(
      (onStoreChange) => {
        const unsubscribe = shouldSubscribe ? observer.subscribe(notifyManager.batchCalls(onStoreChange)) : noop$1;
        observer.updateResult();
        return unsubscribe;
      },
      [observer, shouldSubscribe]
    ),
    () => observer.getCurrentResult(),
    () => observer.getCurrentResult()
  );
  reactExports.useEffect(() => {
    observer.setOptions(defaultedOptions);
  }, [defaultedOptions, observer]);
  if (shouldSuspend(defaultedOptions, result)) {
    throw fetchOptimistic(defaultedOptions, observer, errorResetBoundary);
  }
  if (getHasError({
    result,
    errorResetBoundary,
    throwOnError: defaultedOptions.throwOnError,
    query,
    suspense: defaultedOptions.suspense
  })) {
    throw result.error;
  }
  client.getDefaultOptions().queries?._experimental_afterQuery?.(
    defaultedOptions,
    result
  );
  if (defaultedOptions.experimental_prefetchInRender && !environmentManager.isServer() && willFetch(result, isRestoring)) {
    const promise = isNewCacheEntry ? (
      // Fetch immediately on render in order to ensure `.promise` is resolved even if the component is unmounted
      fetchOptimistic(defaultedOptions, observer, errorResetBoundary)
    ) : (
      // subscribe to the "cache promise" so that we can finalize the currentThenable once data comes in
      query?.promise
    );
    promise?.catch(noop$1).finally(() => {
      observer.updateResult();
    });
  }
  return !defaultedOptions.notifyOnChangeProps ? observer.trackResult(result) : result;
}
function useQuery(options, queryClient) {
  return useBaseQuery(options, QueryObserver);
}
const appCss = "/assets/styles-DwM8hKnt.css";
function bind(fn, thisArg) {
  return function wrap() {
    return fn.apply(thisArg, arguments);
  };
}
const { toString } = Object.prototype;
const { getPrototypeOf } = Object;
const { iterator, toStringTag } = Symbol;
const kindOf = /* @__PURE__ */ ((cache) => (thing) => {
  const str = toString.call(thing);
  return cache[str] || (cache[str] = str.slice(8, -1).toLowerCase());
})(/* @__PURE__ */ Object.create(null));
const kindOfTest = (type2) => {
  type2 = type2.toLowerCase();
  return (thing) => kindOf(thing) === type2;
};
const typeOfTest = (type2) => (thing) => typeof thing === type2;
const { isArray } = Array;
const isUndefined = typeOfTest("undefined");
function isBuffer(val) {
  return val !== null && !isUndefined(val) && val.constructor !== null && !isUndefined(val.constructor) && isFunction$1(val.constructor.isBuffer) && val.constructor.isBuffer(val);
}
const isArrayBuffer = kindOfTest("ArrayBuffer");
function isArrayBufferView(val) {
  let result;
  if (typeof ArrayBuffer !== "undefined" && ArrayBuffer.isView) {
    result = ArrayBuffer.isView(val);
  } else {
    result = val && val.buffer && isArrayBuffer(val.buffer);
  }
  return result;
}
const isString = typeOfTest("string");
const isFunction$1 = typeOfTest("function");
const isNumber = typeOfTest("number");
const isObject = (thing) => thing !== null && typeof thing === "object";
const isBoolean = (thing) => thing === true || thing === false;
const isPlainObject = (val) => {
  if (kindOf(val) !== "object") {
    return false;
  }
  const prototype2 = getPrototypeOf(val);
  return (prototype2 === null || prototype2 === Object.prototype || Object.getPrototypeOf(prototype2) === null) && !(toStringTag in val) && !(iterator in val);
};
const isEmptyObject = (val) => {
  if (!isObject(val) || isBuffer(val)) {
    return false;
  }
  try {
    return Object.keys(val).length === 0 && Object.getPrototypeOf(val) === Object.prototype;
  } catch (e) {
    return false;
  }
};
const isDate = kindOfTest("Date");
const isFile = kindOfTest("File");
const isReactNativeBlob = (value) => {
  return !!(value && typeof value.uri !== "undefined");
};
const isReactNative = (formData) => formData && typeof formData.getParts !== "undefined";
const isBlob = kindOfTest("Blob");
const isFileList = kindOfTest("FileList");
const isStream = (val) => isObject(val) && isFunction$1(val.pipe);
function getGlobal() {
  if (typeof globalThis !== "undefined") return globalThis;
  if (typeof self !== "undefined") return self;
  if (typeof window !== "undefined") return window;
  if (typeof global !== "undefined") return global;
  return {};
}
const G = getGlobal();
const FormDataCtor = typeof G.FormData !== "undefined" ? G.FormData : void 0;
const isFormData = (thing) => {
  if (!thing) return false;
  if (FormDataCtor && thing instanceof FormDataCtor) return true;
  const proto = getPrototypeOf(thing);
  if (!proto || proto === Object.prototype) return false;
  if (!isFunction$1(thing.append)) return false;
  const kind = kindOf(thing);
  return kind === "formdata" || // detect form-data instance
  kind === "object" && isFunction$1(thing.toString) && thing.toString() === "[object FormData]";
};
const isURLSearchParams = kindOfTest("URLSearchParams");
const [isReadableStream, isRequest, isResponse, isHeaders] = [
  "ReadableStream",
  "Request",
  "Response",
  "Headers"
].map(kindOfTest);
const trim = (str) => {
  return str.trim ? str.trim() : str.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, "");
};
function forEach(obj, fn, { allOwnKeys = false } = {}) {
  if (obj === null || typeof obj === "undefined") {
    return;
  }
  let i;
  let l;
  if (typeof obj !== "object") {
    obj = [obj];
  }
  if (isArray(obj)) {
    for (i = 0, l = obj.length; i < l; i++) {
      fn.call(null, obj[i], i, obj);
    }
  } else {
    if (isBuffer(obj)) {
      return;
    }
    const keys = allOwnKeys ? Object.getOwnPropertyNames(obj) : Object.keys(obj);
    const len = keys.length;
    let key;
    for (i = 0; i < len; i++) {
      key = keys[i];
      fn.call(null, obj[key], key, obj);
    }
  }
}
function findKey(obj, key) {
  if (isBuffer(obj)) {
    return null;
  }
  key = key.toLowerCase();
  const keys = Object.keys(obj);
  let i = keys.length;
  let _key;
  while (i-- > 0) {
    _key = keys[i];
    if (key === _key.toLowerCase()) {
      return _key;
    }
  }
  return null;
}
const _global = (() => {
  if (typeof globalThis !== "undefined") return globalThis;
  return typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : global;
})();
const isContextDefined = (context) => !isUndefined(context) && context !== _global;
function merge(...objs) {
  const { caseless, skipUndefined } = isContextDefined(this) && this || {};
  const result = {};
  const assignValue = (val, key) => {
    if (key === "__proto__" || key === "constructor" || key === "prototype") {
      return;
    }
    const targetKey = caseless && findKey(result, key) || key;
    const existing = hasOwnProperty(result, targetKey) ? result[targetKey] : void 0;
    if (isPlainObject(existing) && isPlainObject(val)) {
      result[targetKey] = merge(existing, val);
    } else if (isPlainObject(val)) {
      result[targetKey] = merge({}, val);
    } else if (isArray(val)) {
      result[targetKey] = val.slice();
    } else if (!skipUndefined || !isUndefined(val)) {
      result[targetKey] = val;
    }
  };
  for (let i = 0, l = objs.length; i < l; i++) {
    objs[i] && forEach(objs[i], assignValue);
  }
  return result;
}
const extend = (a, b, thisArg, { allOwnKeys } = {}) => {
  forEach(
    b,
    (val, key) => {
      if (thisArg && isFunction$1(val)) {
        Object.defineProperty(a, key, {
          // Null-proto descriptor so a polluted Object.prototype.get cannot
          // hijack defineProperty's accessor-vs-data resolution.
          __proto__: null,
          value: bind(val, thisArg),
          writable: true,
          enumerable: true,
          configurable: true
        });
      } else {
        Object.defineProperty(a, key, {
          __proto__: null,
          value: val,
          writable: true,
          enumerable: true,
          configurable: true
        });
      }
    },
    { allOwnKeys }
  );
  return a;
};
const stripBOM = (content) => {
  if (content.charCodeAt(0) === 65279) {
    content = content.slice(1);
  }
  return content;
};
const inherits = (constructor, superConstructor, props, descriptors) => {
  constructor.prototype = Object.create(superConstructor.prototype, descriptors);
  Object.defineProperty(constructor.prototype, "constructor", {
    __proto__: null,
    value: constructor,
    writable: true,
    enumerable: false,
    configurable: true
  });
  Object.defineProperty(constructor, "super", {
    __proto__: null,
    value: superConstructor.prototype
  });
  props && Object.assign(constructor.prototype, props);
};
const toFlatObject = (sourceObj, destObj, filter2, propFilter) => {
  let props;
  let i;
  let prop;
  const merged = {};
  destObj = destObj || {};
  if (sourceObj == null) return destObj;
  do {
    props = Object.getOwnPropertyNames(sourceObj);
    i = props.length;
    while (i-- > 0) {
      prop = props[i];
      if ((!propFilter || propFilter(prop, sourceObj, destObj)) && !merged[prop]) {
        destObj[prop] = sourceObj[prop];
        merged[prop] = true;
      }
    }
    sourceObj = filter2 !== false && getPrototypeOf(sourceObj);
  } while (sourceObj && (!filter2 || filter2(sourceObj, destObj)) && sourceObj !== Object.prototype);
  return destObj;
};
const endsWith = (str, searchString, position) => {
  str = String(str);
  if (position === void 0 || position > str.length) {
    position = str.length;
  }
  position -= searchString.length;
  const lastIndex = str.indexOf(searchString, position);
  return lastIndex !== -1 && lastIndex === position;
};
const toArray = (thing) => {
  if (!thing) return null;
  if (isArray(thing)) return thing;
  let i = thing.length;
  if (!isNumber(i)) return null;
  const arr = new Array(i);
  while (i-- > 0) {
    arr[i] = thing[i];
  }
  return arr;
};
const isTypedArray = /* @__PURE__ */ ((TypedArray) => {
  return (thing) => {
    return TypedArray && thing instanceof TypedArray;
  };
})(typeof Uint8Array !== "undefined" && getPrototypeOf(Uint8Array));
const forEachEntry = (obj, fn) => {
  const generator = obj && obj[iterator];
  const _iterator = generator.call(obj);
  let result;
  while ((result = _iterator.next()) && !result.done) {
    const pair = result.value;
    fn.call(obj, pair[0], pair[1]);
  }
};
const matchAll = (regExp, str) => {
  let matches;
  const arr = [];
  while ((matches = regExp.exec(str)) !== null) {
    arr.push(matches);
  }
  return arr;
};
const isHTMLForm = kindOfTest("HTMLFormElement");
const toCamelCase$1 = (str) => {
  return str.toLowerCase().replace(/[-_\s]([a-z\d])(\w*)/g, function replacer(m, p1, p2) {
    return p1.toUpperCase() + p2;
  });
};
const hasOwnProperty = (({ hasOwnProperty: hasOwnProperty2 }) => (obj, prop) => hasOwnProperty2.call(obj, prop))(Object.prototype);
const isRegExp = kindOfTest("RegExp");
const reduceDescriptors = (obj, reducer) => {
  const descriptors = Object.getOwnPropertyDescriptors(obj);
  const reducedDescriptors = {};
  forEach(descriptors, (descriptor, name) => {
    let ret;
    if ((ret = reducer(descriptor, name, obj)) !== false) {
      reducedDescriptors[name] = ret || descriptor;
    }
  });
  Object.defineProperties(obj, reducedDescriptors);
};
const freezeMethods = (obj) => {
  reduceDescriptors(obj, (descriptor, name) => {
    if (isFunction$1(obj) && ["arguments", "caller", "callee"].includes(name)) {
      return false;
    }
    const value = obj[name];
    if (!isFunction$1(value)) return;
    descriptor.enumerable = false;
    if ("writable" in descriptor) {
      descriptor.writable = false;
      return;
    }
    if (!descriptor.set) {
      descriptor.set = () => {
        throw Error("Can not rewrite read-only method '" + name + "'");
      };
    }
  });
};
const toObjectSet = (arrayOrString, delimiter) => {
  const obj = {};
  const define = (arr) => {
    arr.forEach((value) => {
      obj[value] = true;
    });
  };
  isArray(arrayOrString) ? define(arrayOrString) : define(String(arrayOrString).split(delimiter));
  return obj;
};
const noop = () => {
};
const toFiniteNumber = (value, defaultValue) => {
  return value != null && Number.isFinite(value = +value) ? value : defaultValue;
};
function isSpecCompliantForm(thing) {
  return !!(thing && isFunction$1(thing.append) && thing[toStringTag] === "FormData" && thing[iterator]);
}
const toJSONObject = (obj) => {
  const stack = new Array(10);
  const visit = (source, i) => {
    if (isObject(source)) {
      if (stack.indexOf(source) >= 0) {
        return;
      }
      if (isBuffer(source)) {
        return source;
      }
      if (!("toJSON" in source)) {
        stack[i] = source;
        const target = isArray(source) ? [] : {};
        forEach(source, (value, key) => {
          const reducedValue = visit(value, i + 1);
          !isUndefined(reducedValue) && (target[key] = reducedValue);
        });
        stack[i] = void 0;
        return target;
      }
    }
    return source;
  };
  return visit(obj, 0);
};
const isAsyncFn = kindOfTest("AsyncFunction");
const isThenable = (thing) => thing && (isObject(thing) || isFunction$1(thing)) && isFunction$1(thing.then) && isFunction$1(thing.catch);
const _setImmediate = ((setImmediateSupported, postMessageSupported) => {
  if (setImmediateSupported) {
    return setImmediate;
  }
  return postMessageSupported ? ((token, callbacks) => {
    _global.addEventListener(
      "message",
      ({ source, data }) => {
        if (source === _global && data === token) {
          callbacks.length && callbacks.shift()();
        }
      },
      false
    );
    return (cb) => {
      callbacks.push(cb);
      _global.postMessage(token, "*");
    };
  })(`axios@${Math.random()}`, []) : (cb) => setTimeout(cb);
})(typeof setImmediate === "function", isFunction$1(_global.postMessage));
const asap = typeof queueMicrotask !== "undefined" ? queueMicrotask.bind(_global) : typeof process !== "undefined" && process.nextTick || _setImmediate;
const isIterable = (thing) => thing != null && isFunction$1(thing[iterator]);
const utils$1 = {
  isArray,
  isArrayBuffer,
  isBuffer,
  isFormData,
  isArrayBufferView,
  isString,
  isNumber,
  isBoolean,
  isObject,
  isPlainObject,
  isEmptyObject,
  isReadableStream,
  isRequest,
  isResponse,
  isHeaders,
  isUndefined,
  isDate,
  isFile,
  isReactNativeBlob,
  isReactNative,
  isBlob,
  isRegExp,
  isFunction: isFunction$1,
  isStream,
  isURLSearchParams,
  isTypedArray,
  isFileList,
  forEach,
  merge,
  extend,
  trim,
  stripBOM,
  inherits,
  toFlatObject,
  kindOf,
  kindOfTest,
  endsWith,
  toArray,
  forEachEntry,
  matchAll,
  isHTMLForm,
  hasOwnProperty,
  hasOwnProp: hasOwnProperty,
  // an alias to avoid ESLint no-prototype-builtins detection
  reduceDescriptors,
  freezeMethods,
  toObjectSet,
  toCamelCase: toCamelCase$1,
  noop,
  toFiniteNumber,
  findKey,
  global: _global,
  isContextDefined,
  isSpecCompliantForm,
  toJSONObject,
  isAsyncFn,
  isThenable,
  setImmediate: _setImmediate,
  asap,
  isIterable
};
const ignoreDuplicateOf = utils$1.toObjectSet([
  "age",
  "authorization",
  "content-length",
  "content-type",
  "etag",
  "expires",
  "from",
  "host",
  "if-modified-since",
  "if-unmodified-since",
  "last-modified",
  "location",
  "max-forwards",
  "proxy-authorization",
  "referer",
  "retry-after",
  "user-agent"
]);
const parseHeaders = (rawHeaders) => {
  const parsed = {};
  let key;
  let val;
  let i;
  rawHeaders && rawHeaders.split("\n").forEach(function parser(line) {
    i = line.indexOf(":");
    key = line.substring(0, i).trim().toLowerCase();
    val = line.substring(i + 1).trim();
    if (!key || parsed[key] && ignoreDuplicateOf[key]) {
      return;
    }
    if (key === "set-cookie") {
      if (parsed[key]) {
        parsed[key].push(val);
      } else {
        parsed[key] = [val];
      }
    } else {
      parsed[key] = parsed[key] ? parsed[key] + ", " + val : val;
    }
  });
  return parsed;
};
const $internals = /* @__PURE__ */ Symbol("internals");
const INVALID_HEADER_VALUE_CHARS_RE = /[^\x09\x20-\x7E\x80-\xFF]/g;
function trimSPorHTAB(str) {
  let start = 0;
  let end = str.length;
  while (start < end) {
    const code = str.charCodeAt(start);
    if (code !== 9 && code !== 32) {
      break;
    }
    start += 1;
  }
  while (end > start) {
    const code = str.charCodeAt(end - 1);
    if (code !== 9 && code !== 32) {
      break;
    }
    end -= 1;
  }
  return start === 0 && end === str.length ? str : str.slice(start, end);
}
function normalizeHeader(header) {
  return header && String(header).trim().toLowerCase();
}
function sanitizeHeaderValue(str) {
  return trimSPorHTAB(str.replace(INVALID_HEADER_VALUE_CHARS_RE, ""));
}
function normalizeValue(value) {
  if (value === false || value == null) {
    return value;
  }
  return utils$1.isArray(value) ? value.map(normalizeValue) : sanitizeHeaderValue(String(value));
}
function parseTokens(str) {
  const tokens = /* @__PURE__ */ Object.create(null);
  const tokensRE = /([^\s,;=]+)\s*(?:=\s*([^,;]+))?/g;
  let match;
  while (match = tokensRE.exec(str)) {
    tokens[match[1]] = match[2];
  }
  return tokens;
}
const isValidHeaderName = (str) => /^[-_a-zA-Z0-9^`|~,!#$%&'*+.]+$/.test(str.trim());
function matchHeaderValue(context, value, header, filter2, isHeaderNameFilter) {
  if (utils$1.isFunction(filter2)) {
    return filter2.call(this, value, header);
  }
  if (isHeaderNameFilter) {
    value = header;
  }
  if (!utils$1.isString(value)) return;
  if (utils$1.isString(filter2)) {
    return value.indexOf(filter2) !== -1;
  }
  if (utils$1.isRegExp(filter2)) {
    return filter2.test(value);
  }
}
function formatHeader(header) {
  return header.trim().toLowerCase().replace(/([a-z\d])(\w*)/g, (w, char, str) => {
    return char.toUpperCase() + str;
  });
}
function buildAccessors(obj, header) {
  const accessorName = utils$1.toCamelCase(" " + header);
  ["get", "set", "has"].forEach((methodName) => {
    Object.defineProperty(obj, methodName + accessorName, {
      // Null-proto descriptor so a polluted Object.prototype.get cannot turn
      // this data descriptor into an accessor descriptor on the way in.
      __proto__: null,
      value: function(arg1, arg2, arg3) {
        return this[methodName].call(this, header, arg1, arg2, arg3);
      },
      configurable: true
    });
  });
}
let AxiosHeaders$1 = class AxiosHeaders {
  constructor(headers) {
    headers && this.set(headers);
  }
  set(header, valueOrRewrite, rewrite) {
    const self2 = this;
    function setHeader(_value, _header, _rewrite) {
      const lHeader = normalizeHeader(_header);
      if (!lHeader) {
        throw new Error("header name must be a non-empty string");
      }
      const key = utils$1.findKey(self2, lHeader);
      if (!key || self2[key] === void 0 || _rewrite === true || _rewrite === void 0 && self2[key] !== false) {
        self2[key || _header] = normalizeValue(_value);
      }
    }
    const setHeaders = (headers, _rewrite) => utils$1.forEach(headers, (_value, _header) => setHeader(_value, _header, _rewrite));
    if (utils$1.isPlainObject(header) || header instanceof this.constructor) {
      setHeaders(header, valueOrRewrite);
    } else if (utils$1.isString(header) && (header = header.trim()) && !isValidHeaderName(header)) {
      setHeaders(parseHeaders(header), valueOrRewrite);
    } else if (utils$1.isObject(header) && utils$1.isIterable(header)) {
      let obj = {}, dest, key;
      for (const entry of header) {
        if (!utils$1.isArray(entry)) {
          throw TypeError("Object iterator must return a key-value pair");
        }
        obj[key = entry[0]] = (dest = obj[key]) ? utils$1.isArray(dest) ? [...dest, entry[1]] : [dest, entry[1]] : entry[1];
      }
      setHeaders(obj, valueOrRewrite);
    } else {
      header != null && setHeader(valueOrRewrite, header, rewrite);
    }
    return this;
  }
  get(header, parser) {
    header = normalizeHeader(header);
    if (header) {
      const key = utils$1.findKey(this, header);
      if (key) {
        const value = this[key];
        if (!parser) {
          return value;
        }
        if (parser === true) {
          return parseTokens(value);
        }
        if (utils$1.isFunction(parser)) {
          return parser.call(this, value, key);
        }
        if (utils$1.isRegExp(parser)) {
          return parser.exec(value);
        }
        throw new TypeError("parser must be boolean|regexp|function");
      }
    }
  }
  has(header, matcher) {
    header = normalizeHeader(header);
    if (header) {
      const key = utils$1.findKey(this, header);
      return !!(key && this[key] !== void 0 && (!matcher || matchHeaderValue(this, this[key], key, matcher)));
    }
    return false;
  }
  delete(header, matcher) {
    const self2 = this;
    let deleted = false;
    function deleteHeader(_header) {
      _header = normalizeHeader(_header);
      if (_header) {
        const key = utils$1.findKey(self2, _header);
        if (key && (!matcher || matchHeaderValue(self2, self2[key], key, matcher))) {
          delete self2[key];
          deleted = true;
        }
      }
    }
    if (utils$1.isArray(header)) {
      header.forEach(deleteHeader);
    } else {
      deleteHeader(header);
    }
    return deleted;
  }
  clear(matcher) {
    const keys = Object.keys(this);
    let i = keys.length;
    let deleted = false;
    while (i--) {
      const key = keys[i];
      if (!matcher || matchHeaderValue(this, this[key], key, matcher, true)) {
        delete this[key];
        deleted = true;
      }
    }
    return deleted;
  }
  normalize(format) {
    const self2 = this;
    const headers = {};
    utils$1.forEach(this, (value, header) => {
      const key = utils$1.findKey(headers, header);
      if (key) {
        self2[key] = normalizeValue(value);
        delete self2[header];
        return;
      }
      const normalized = format ? formatHeader(header) : String(header).trim();
      if (normalized !== header) {
        delete self2[header];
      }
      self2[normalized] = normalizeValue(value);
      headers[normalized] = true;
    });
    return this;
  }
  concat(...targets) {
    return this.constructor.concat(this, ...targets);
  }
  toJSON(asStrings) {
    const obj = /* @__PURE__ */ Object.create(null);
    utils$1.forEach(this, (value, header) => {
      value != null && value !== false && (obj[header] = asStrings && utils$1.isArray(value) ? value.join(", ") : value);
    });
    return obj;
  }
  [Symbol.iterator]() {
    return Object.entries(this.toJSON())[Symbol.iterator]();
  }
  toString() {
    return Object.entries(this.toJSON()).map(([header, value]) => header + ": " + value).join("\n");
  }
  getSetCookie() {
    return this.get("set-cookie") || [];
  }
  get [Symbol.toStringTag]() {
    return "AxiosHeaders";
  }
  static from(thing) {
    return thing instanceof this ? thing : new this(thing);
  }
  static concat(first, ...targets) {
    const computed = new this(first);
    targets.forEach((target) => computed.set(target));
    return computed;
  }
  static accessor(header) {
    const internals = this[$internals] = this[$internals] = {
      accessors: {}
    };
    const accessors = internals.accessors;
    const prototype2 = this.prototype;
    function defineAccessor(_header) {
      const lHeader = normalizeHeader(_header);
      if (!accessors[lHeader]) {
        buildAccessors(prototype2, _header);
        accessors[lHeader] = true;
      }
    }
    utils$1.isArray(header) ? header.forEach(defineAccessor) : defineAccessor(header);
    return this;
  }
};
AxiosHeaders$1.accessor([
  "Content-Type",
  "Content-Length",
  "Accept",
  "Accept-Encoding",
  "User-Agent",
  "Authorization"
]);
utils$1.reduceDescriptors(AxiosHeaders$1.prototype, ({ value }, key) => {
  let mapped = key[0].toUpperCase() + key.slice(1);
  return {
    get: () => value,
    set(headerValue) {
      this[mapped] = headerValue;
    }
  };
});
utils$1.freezeMethods(AxiosHeaders$1);
const REDACTED = "[REDACTED ****]";
function hasOwnOrPrototypeToJSON(source) {
  if (utils$1.hasOwnProp(source, "toJSON")) {
    return true;
  }
  let prototype2 = Object.getPrototypeOf(source);
  while (prototype2 && prototype2 !== Object.prototype) {
    if (utils$1.hasOwnProp(prototype2, "toJSON")) {
      return true;
    }
    prototype2 = Object.getPrototypeOf(prototype2);
  }
  return false;
}
function redactConfig(config, redactKeys) {
  const lowerKeys = new Set(redactKeys.map((k) => String(k).toLowerCase()));
  const seen = [];
  const visit = (source) => {
    if (source === null || typeof source !== "object") return source;
    if (utils$1.isBuffer(source)) return source;
    if (seen.indexOf(source) !== -1) return void 0;
    if (source instanceof AxiosHeaders$1) {
      source = source.toJSON();
    }
    seen.push(source);
    let result;
    if (utils$1.isArray(source)) {
      result = [];
      source.forEach((v, i) => {
        const reducedValue = visit(v);
        if (!utils$1.isUndefined(reducedValue)) {
          result[i] = reducedValue;
        }
      });
    } else {
      if (!utils$1.isPlainObject(source) && hasOwnOrPrototypeToJSON(source)) {
        seen.pop();
        return source;
      }
      result = /* @__PURE__ */ Object.create(null);
      for (const [key, value] of Object.entries(source)) {
        const reducedValue = lowerKeys.has(key.toLowerCase()) ? REDACTED : visit(value);
        if (!utils$1.isUndefined(reducedValue)) {
          result[key] = reducedValue;
        }
      }
    }
    seen.pop();
    return result;
  };
  return visit(config);
}
let AxiosError$1 = class AxiosError extends Error {
  static from(error, code, config, request, response, customProps) {
    const axiosError = new AxiosError(error.message, code || error.code, config, request, response);
    axiosError.cause = error;
    axiosError.name = error.name;
    if (error.status != null && axiosError.status == null) {
      axiosError.status = error.status;
    }
    customProps && Object.assign(axiosError, customProps);
    return axiosError;
  }
  /**
   * Create an Error with the specified message, config, error code, request and response.
   *
   * @param {string} message The error message.
   * @param {string} [code] The error code (for example, 'ECONNABORTED').
   * @param {Object} [config] The config.
   * @param {Object} [request] The request.
   * @param {Object} [response] The response.
   *
   * @returns {Error} The created error.
   */
  constructor(message, code, config, request, response) {
    super(message);
    Object.defineProperty(this, "message", {
      // Null-proto descriptor so a polluted Object.prototype.get cannot turn
      // this data descriptor into an accessor descriptor on the way in.
      __proto__: null,
      value: message,
      enumerable: true,
      writable: true,
      configurable: true
    });
    this.name = "AxiosError";
    this.isAxiosError = true;
    code && (this.code = code);
    config && (this.config = config);
    request && (this.request = request);
    if (response) {
      this.response = response;
      this.status = response.status;
    }
  }
  toJSON() {
    const config = this.config;
    const redactKeys = config && utils$1.hasOwnProp(config, "redact") ? config.redact : void 0;
    const serializedConfig = utils$1.isArray(redactKeys) && redactKeys.length > 0 ? redactConfig(config, redactKeys) : utils$1.toJSONObject(config);
    return {
      // Standard
      message: this.message,
      name: this.name,
      // Microsoft
      description: this.description,
      number: this.number,
      // Mozilla
      fileName: this.fileName,
      lineNumber: this.lineNumber,
      columnNumber: this.columnNumber,
      stack: this.stack,
      // Axios
      config: serializedConfig,
      code: this.code,
      status: this.status
    };
  }
};
AxiosError$1.ERR_BAD_OPTION_VALUE = "ERR_BAD_OPTION_VALUE";
AxiosError$1.ERR_BAD_OPTION = "ERR_BAD_OPTION";
AxiosError$1.ECONNABORTED = "ECONNABORTED";
AxiosError$1.ETIMEDOUT = "ETIMEDOUT";
AxiosError$1.ECONNREFUSED = "ECONNREFUSED";
AxiosError$1.ERR_NETWORK = "ERR_NETWORK";
AxiosError$1.ERR_FR_TOO_MANY_REDIRECTS = "ERR_FR_TOO_MANY_REDIRECTS";
AxiosError$1.ERR_DEPRECATED = "ERR_DEPRECATED";
AxiosError$1.ERR_BAD_RESPONSE = "ERR_BAD_RESPONSE";
AxiosError$1.ERR_BAD_REQUEST = "ERR_BAD_REQUEST";
AxiosError$1.ERR_CANCELED = "ERR_CANCELED";
AxiosError$1.ERR_NOT_SUPPORT = "ERR_NOT_SUPPORT";
AxiosError$1.ERR_INVALID_URL = "ERR_INVALID_URL";
AxiosError$1.ERR_FORM_DATA_DEPTH_EXCEEDED = "ERR_FORM_DATA_DEPTH_EXCEEDED";
var delayed_stream;
var hasRequiredDelayed_stream;
function requireDelayed_stream() {
  if (hasRequiredDelayed_stream) return delayed_stream;
  hasRequiredDelayed_stream = 1;
  var Stream = stream.Stream;
  var util = require$$1;
  delayed_stream = DelayedStream;
  function DelayedStream() {
    this.source = null;
    this.dataSize = 0;
    this.maxDataSize = 1024 * 1024;
    this.pauseStream = true;
    this._maxDataSizeExceeded = false;
    this._released = false;
    this._bufferedEvents = [];
  }
  util.inherits(DelayedStream, Stream);
  DelayedStream.create = function(source, options) {
    var delayedStream = new this();
    options = options || {};
    for (var option in options) {
      delayedStream[option] = options[option];
    }
    delayedStream.source = source;
    var realEmit = source.emit;
    source.emit = function() {
      delayedStream._handleEmit(arguments);
      return realEmit.apply(source, arguments);
    };
    source.on("error", function() {
    });
    if (delayedStream.pauseStream) {
      source.pause();
    }
    return delayedStream;
  };
  Object.defineProperty(DelayedStream.prototype, "readable", {
    configurable: true,
    enumerable: true,
    get: function() {
      return this.source.readable;
    }
  });
  DelayedStream.prototype.setEncoding = function() {
    return this.source.setEncoding.apply(this.source, arguments);
  };
  DelayedStream.prototype.resume = function() {
    if (!this._released) {
      this.release();
    }
    this.source.resume();
  };
  DelayedStream.prototype.pause = function() {
    this.source.pause();
  };
  DelayedStream.prototype.release = function() {
    this._released = true;
    this._bufferedEvents.forEach(function(args) {
      this.emit.apply(this, args);
    }.bind(this));
    this._bufferedEvents = [];
  };
  DelayedStream.prototype.pipe = function() {
    var r = Stream.prototype.pipe.apply(this, arguments);
    this.resume();
    return r;
  };
  DelayedStream.prototype._handleEmit = function(args) {
    if (this._released) {
      this.emit.apply(this, args);
      return;
    }
    if (args[0] === "data") {
      this.dataSize += args[1].length;
      this._checkIfMaxDataSizeExceeded();
    }
    this._bufferedEvents.push(args);
  };
  DelayedStream.prototype._checkIfMaxDataSizeExceeded = function() {
    if (this._maxDataSizeExceeded) {
      return;
    }
    if (this.dataSize <= this.maxDataSize) {
      return;
    }
    this._maxDataSizeExceeded = true;
    var message = "DelayedStream#maxDataSize of " + this.maxDataSize + " bytes exceeded.";
    this.emit("error", new Error(message));
  };
  return delayed_stream;
}
var combined_stream;
var hasRequiredCombined_stream;
function requireCombined_stream() {
  if (hasRequiredCombined_stream) return combined_stream;
  hasRequiredCombined_stream = 1;
  var util = require$$1;
  var Stream = stream.Stream;
  var DelayedStream = requireDelayed_stream();
  combined_stream = CombinedStream;
  function CombinedStream() {
    this.writable = false;
    this.readable = true;
    this.dataSize = 0;
    this.maxDataSize = 2 * 1024 * 1024;
    this.pauseStreams = true;
    this._released = false;
    this._streams = [];
    this._currentStream = null;
    this._insideLoop = false;
    this._pendingNext = false;
  }
  util.inherits(CombinedStream, Stream);
  CombinedStream.create = function(options) {
    var combinedStream = new this();
    options = options || {};
    for (var option in options) {
      combinedStream[option] = options[option];
    }
    return combinedStream;
  };
  CombinedStream.isStreamLike = function(stream2) {
    return typeof stream2 !== "function" && typeof stream2 !== "string" && typeof stream2 !== "boolean" && typeof stream2 !== "number" && !Buffer.isBuffer(stream2);
  };
  CombinedStream.prototype.append = function(stream2) {
    var isStreamLike = CombinedStream.isStreamLike(stream2);
    if (isStreamLike) {
      if (!(stream2 instanceof DelayedStream)) {
        var newStream = DelayedStream.create(stream2, {
          maxDataSize: Infinity,
          pauseStream: this.pauseStreams
        });
        stream2.on("data", this._checkDataSize.bind(this));
        stream2 = newStream;
      }
      this._handleErrors(stream2);
      if (this.pauseStreams) {
        stream2.pause();
      }
    }
    this._streams.push(stream2);
    return this;
  };
  CombinedStream.prototype.pipe = function(dest, options) {
    Stream.prototype.pipe.call(this, dest, options);
    this.resume();
    return dest;
  };
  CombinedStream.prototype._getNext = function() {
    this._currentStream = null;
    if (this._insideLoop) {
      this._pendingNext = true;
      return;
    }
    this._insideLoop = true;
    try {
      do {
        this._pendingNext = false;
        this._realGetNext();
      } while (this._pendingNext);
    } finally {
      this._insideLoop = false;
    }
  };
  CombinedStream.prototype._realGetNext = function() {
    var stream2 = this._streams.shift();
    if (typeof stream2 == "undefined") {
      this.end();
      return;
    }
    if (typeof stream2 !== "function") {
      this._pipeNext(stream2);
      return;
    }
    var getStream = stream2;
    getStream(function(stream3) {
      var isStreamLike = CombinedStream.isStreamLike(stream3);
      if (isStreamLike) {
        stream3.on("data", this._checkDataSize.bind(this));
        this._handleErrors(stream3);
      }
      this._pipeNext(stream3);
    }.bind(this));
  };
  CombinedStream.prototype._pipeNext = function(stream2) {
    this._currentStream = stream2;
    var isStreamLike = CombinedStream.isStreamLike(stream2);
    if (isStreamLike) {
      stream2.on("end", this._getNext.bind(this));
      stream2.pipe(this, { end: false });
      return;
    }
    var value = stream2;
    this.write(value);
    this._getNext();
  };
  CombinedStream.prototype._handleErrors = function(stream2) {
    var self2 = this;
    stream2.on("error", function(err) {
      self2._emitError(err);
    });
  };
  CombinedStream.prototype.write = function(data) {
    this.emit("data", data);
  };
  CombinedStream.prototype.pause = function() {
    if (!this.pauseStreams) {
      return;
    }
    if (this.pauseStreams && this._currentStream && typeof this._currentStream.pause == "function") this._currentStream.pause();
    this.emit("pause");
  };
  CombinedStream.prototype.resume = function() {
    if (!this._released) {
      this._released = true;
      this.writable = true;
      this._getNext();
    }
    if (this.pauseStreams && this._currentStream && typeof this._currentStream.resume == "function") this._currentStream.resume();
    this.emit("resume");
  };
  CombinedStream.prototype.end = function() {
    this._reset();
    this.emit("end");
  };
  CombinedStream.prototype.destroy = function() {
    this._reset();
    this.emit("close");
  };
  CombinedStream.prototype._reset = function() {
    this.writable = false;
    this._streams = [];
    this._currentStream = null;
  };
  CombinedStream.prototype._checkDataSize = function() {
    this._updateDataSize();
    if (this.dataSize <= this.maxDataSize) {
      return;
    }
    var message = "DelayedStream#maxDataSize of " + this.maxDataSize + " bytes exceeded.";
    this._emitError(new Error(message));
  };
  CombinedStream.prototype._updateDataSize = function() {
    this.dataSize = 0;
    var self2 = this;
    this._streams.forEach(function(stream2) {
      if (!stream2.dataSize) {
        return;
      }
      self2.dataSize += stream2.dataSize;
    });
    if (this._currentStream && this._currentStream.dataSize) {
      this.dataSize += this._currentStream.dataSize;
    }
  };
  CombinedStream.prototype._emitError = function(err) {
    this._reset();
    this.emit("error", err);
  };
  return combined_stream;
}
var mimeTypes = {};
const require$$0$1 = {
  "application/1d-interleaved-parityfec": { "source": "iana" },
  "application/3gpdash-qoe-report+xml": { "source": "iana", "charset": "UTF-8", "compressible": true },
  "application/3gpp-ims+xml": { "source": "iana", "compressible": true },
  "application/3gpphal+json": { "source": "iana", "compressible": true },
  "application/3gpphalforms+json": { "source": "iana", "compressible": true },
  "application/a2l": { "source": "iana" },
  "application/ace+cbor": { "source": "iana" },
  "application/activemessage": { "source": "iana" },
  "application/activity+json": { "source": "iana", "compressible": true },
  "application/alto-costmap+json": { "source": "iana", "compressible": true },
  "application/alto-costmapfilter+json": { "source": "iana", "compressible": true },
  "application/alto-directory+json": { "source": "iana", "compressible": true },
  "application/alto-endpointcost+json": { "source": "iana", "compressible": true },
  "application/alto-endpointcostparams+json": { "source": "iana", "compressible": true },
  "application/alto-endpointprop+json": { "source": "iana", "compressible": true },
  "application/alto-endpointpropparams+json": { "source": "iana", "compressible": true },
  "application/alto-error+json": { "source": "iana", "compressible": true },
  "application/alto-networkmap+json": { "source": "iana", "compressible": true },
  "application/alto-networkmapfilter+json": { "source": "iana", "compressible": true },
  "application/alto-updatestreamcontrol+json": { "source": "iana", "compressible": true },
  "application/alto-updatestreamparams+json": { "source": "iana", "compressible": true },
  "application/aml": { "source": "iana" },
  "application/andrew-inset": { "source": "iana", "extensions": ["ez"] },
  "application/applefile": { "source": "iana" },
  "application/applixware": { "source": "apache", "extensions": ["aw"] },
  "application/at+jwt": { "source": "iana" },
  "application/atf": { "source": "iana" },
  "application/atfx": { "source": "iana" },
  "application/atom+xml": { "source": "iana", "compressible": true, "extensions": ["atom"] },
  "application/atomcat+xml": { "source": "iana", "compressible": true, "extensions": ["atomcat"] },
  "application/atomdeleted+xml": { "source": "iana", "compressible": true, "extensions": ["atomdeleted"] },
  "application/atomicmail": { "source": "iana" },
  "application/atomsvc+xml": { "source": "iana", "compressible": true, "extensions": ["atomsvc"] },
  "application/atsc-dwd+xml": { "source": "iana", "compressible": true, "extensions": ["dwd"] },
  "application/atsc-dynamic-event-message": { "source": "iana" },
  "application/atsc-held+xml": { "source": "iana", "compressible": true, "extensions": ["held"] },
  "application/atsc-rdt+json": { "source": "iana", "compressible": true },
  "application/atsc-rsat+xml": { "source": "iana", "compressible": true, "extensions": ["rsat"] },
  "application/atxml": { "source": "iana" },
  "application/auth-policy+xml": { "source": "iana", "compressible": true },
  "application/bacnet-xdd+zip": { "source": "iana", "compressible": false },
  "application/batch-smtp": { "source": "iana" },
  "application/bdoc": { "compressible": false, "extensions": ["bdoc"] },
  "application/beep+xml": { "source": "iana", "charset": "UTF-8", "compressible": true },
  "application/calendar+json": { "source": "iana", "compressible": true },
  "application/calendar+xml": { "source": "iana", "compressible": true, "extensions": ["xcs"] },
  "application/call-completion": { "source": "iana" },
  "application/cals-1840": { "source": "iana" },
  "application/captive+json": { "source": "iana", "compressible": true },
  "application/cbor": { "source": "iana" },
  "application/cbor-seq": { "source": "iana" },
  "application/cccex": { "source": "iana" },
  "application/ccmp+xml": { "source": "iana", "compressible": true },
  "application/ccxml+xml": { "source": "iana", "compressible": true, "extensions": ["ccxml"] },
  "application/cdfx+xml": { "source": "iana", "compressible": true, "extensions": ["cdfx"] },
  "application/cdmi-capability": { "source": "iana", "extensions": ["cdmia"] },
  "application/cdmi-container": { "source": "iana", "extensions": ["cdmic"] },
  "application/cdmi-domain": { "source": "iana", "extensions": ["cdmid"] },
  "application/cdmi-object": { "source": "iana", "extensions": ["cdmio"] },
  "application/cdmi-queue": { "source": "iana", "extensions": ["cdmiq"] },
  "application/cdni": { "source": "iana" },
  "application/cea": { "source": "iana" },
  "application/cea-2018+xml": { "source": "iana", "compressible": true },
  "application/cellml+xml": { "source": "iana", "compressible": true },
  "application/cfw": { "source": "iana" },
  "application/city+json": { "source": "iana", "compressible": true },
  "application/clr": { "source": "iana" },
  "application/clue+xml": { "source": "iana", "compressible": true },
  "application/clue_info+xml": { "source": "iana", "compressible": true },
  "application/cms": { "source": "iana" },
  "application/cnrp+xml": { "source": "iana", "compressible": true },
  "application/coap-group+json": { "source": "iana", "compressible": true },
  "application/coap-payload": { "source": "iana" },
  "application/commonground": { "source": "iana" },
  "application/conference-info+xml": { "source": "iana", "compressible": true },
  "application/cose": { "source": "iana" },
  "application/cose-key": { "source": "iana" },
  "application/cose-key-set": { "source": "iana" },
  "application/cpl+xml": { "source": "iana", "compressible": true, "extensions": ["cpl"] },
  "application/csrattrs": { "source": "iana" },
  "application/csta+xml": { "source": "iana", "compressible": true },
  "application/cstadata+xml": { "source": "iana", "compressible": true },
  "application/csvm+json": { "source": "iana", "compressible": true },
  "application/cu-seeme": { "source": "apache", "extensions": ["cu"] },
  "application/cwt": { "source": "iana" },
  "application/cybercash": { "source": "iana" },
  "application/dart": { "compressible": true },
  "application/dash+xml": { "source": "iana", "compressible": true, "extensions": ["mpd"] },
  "application/dash-patch+xml": { "source": "iana", "compressible": true, "extensions": ["mpp"] },
  "application/dashdelta": { "source": "iana" },
  "application/davmount+xml": { "source": "iana", "compressible": true, "extensions": ["davmount"] },
  "application/dca-rft": { "source": "iana" },
  "application/dcd": { "source": "iana" },
  "application/dec-dx": { "source": "iana" },
  "application/dialog-info+xml": { "source": "iana", "compressible": true },
  "application/dicom": { "source": "iana" },
  "application/dicom+json": { "source": "iana", "compressible": true },
  "application/dicom+xml": { "source": "iana", "compressible": true },
  "application/dii": { "source": "iana" },
  "application/dit": { "source": "iana" },
  "application/dns": { "source": "iana" },
  "application/dns+json": { "source": "iana", "compressible": true },
  "application/dns-message": { "source": "iana" },
  "application/docbook+xml": { "source": "apache", "compressible": true, "extensions": ["dbk"] },
  "application/dots+cbor": { "source": "iana" },
  "application/dskpp+xml": { "source": "iana", "compressible": true },
  "application/dssc+der": { "source": "iana", "extensions": ["dssc"] },
  "application/dssc+xml": { "source": "iana", "compressible": true, "extensions": ["xdssc"] },
  "application/dvcs": { "source": "iana" },
  "application/ecmascript": { "source": "iana", "compressible": true, "extensions": ["es", "ecma"] },
  "application/edi-consent": { "source": "iana" },
  "application/edi-x12": { "source": "iana", "compressible": false },
  "application/edifact": { "source": "iana", "compressible": false },
  "application/efi": { "source": "iana" },
  "application/elm+json": { "source": "iana", "charset": "UTF-8", "compressible": true },
  "application/elm+xml": { "source": "iana", "compressible": true },
  "application/emergencycalldata.cap+xml": { "source": "iana", "charset": "UTF-8", "compressible": true },
  "application/emergencycalldata.comment+xml": { "source": "iana", "compressible": true },
  "application/emergencycalldata.control+xml": { "source": "iana", "compressible": true },
  "application/emergencycalldata.deviceinfo+xml": { "source": "iana", "compressible": true },
  "application/emergencycalldata.ecall.msd": { "source": "iana" },
  "application/emergencycalldata.providerinfo+xml": { "source": "iana", "compressible": true },
  "application/emergencycalldata.serviceinfo+xml": { "source": "iana", "compressible": true },
  "application/emergencycalldata.subscriberinfo+xml": { "source": "iana", "compressible": true },
  "application/emergencycalldata.veds+xml": { "source": "iana", "compressible": true },
  "application/emma+xml": { "source": "iana", "compressible": true, "extensions": ["emma"] },
  "application/emotionml+xml": { "source": "iana", "compressible": true, "extensions": ["emotionml"] },
  "application/encaprtp": { "source": "iana" },
  "application/epp+xml": { "source": "iana", "compressible": true },
  "application/epub+zip": { "source": "iana", "compressible": false, "extensions": ["epub"] },
  "application/eshop": { "source": "iana" },
  "application/exi": { "source": "iana", "extensions": ["exi"] },
  "application/expect-ct-report+json": { "source": "iana", "compressible": true },
  "application/express": { "source": "iana", "extensions": ["exp"] },
  "application/fastinfoset": { "source": "iana" },
  "application/fastsoap": { "source": "iana" },
  "application/fdt+xml": { "source": "iana", "compressible": true, "extensions": ["fdt"] },
  "application/fhir+json": { "source": "iana", "charset": "UTF-8", "compressible": true },
  "application/fhir+xml": { "source": "iana", "charset": "UTF-8", "compressible": true },
  "application/fido.trusted-apps+json": { "compressible": true },
  "application/fits": { "source": "iana" },
  "application/flexfec": { "source": "iana" },
  "application/font-sfnt": { "source": "iana" },
  "application/font-tdpfr": { "source": "iana", "extensions": ["pfr"] },
  "application/font-woff": { "source": "iana", "compressible": false },
  "application/framework-attributes+xml": { "source": "iana", "compressible": true },
  "application/geo+json": { "source": "iana", "compressible": true, "extensions": ["geojson"] },
  "application/geo+json-seq": { "source": "iana" },
  "application/geopackage+sqlite3": { "source": "iana" },
  "application/geoxacml+xml": { "source": "iana", "compressible": true },
  "application/gltf-buffer": { "source": "iana" },
  "application/gml+xml": { "source": "iana", "compressible": true, "extensions": ["gml"] },
  "application/gpx+xml": { "source": "apache", "compressible": true, "extensions": ["gpx"] },
  "application/gxf": { "source": "apache", "extensions": ["gxf"] },
  "application/gzip": { "source": "iana", "compressible": false, "extensions": ["gz"] },
  "application/h224": { "source": "iana" },
  "application/held+xml": { "source": "iana", "compressible": true },
  "application/hjson": { "extensions": ["hjson"] },
  "application/http": { "source": "iana" },
  "application/hyperstudio": { "source": "iana", "extensions": ["stk"] },
  "application/ibe-key-request+xml": { "source": "iana", "compressible": true },
  "application/ibe-pkg-reply+xml": { "source": "iana", "compressible": true },
  "application/ibe-pp-data": { "source": "iana" },
  "application/iges": { "source": "iana" },
  "application/im-iscomposing+xml": { "source": "iana", "charset": "UTF-8", "compressible": true },
  "application/index": { "source": "iana" },
  "application/index.cmd": { "source": "iana" },
  "application/index.obj": { "source": "iana" },
  "application/index.response": { "source": "iana" },
  "application/index.vnd": { "source": "iana" },
  "application/inkml+xml": { "source": "iana", "compressible": true, "extensions": ["ink", "inkml"] },
  "application/iotp": { "source": "iana" },
  "application/ipfix": { "source": "iana", "extensions": ["ipfix"] },
  "application/ipp": { "source": "iana" },
  "application/isup": { "source": "iana" },
  "application/its+xml": { "source": "iana", "compressible": true, "extensions": ["its"] },
  "application/java-archive": { "source": "apache", "compressible": false, "extensions": ["jar", "war", "ear"] },
  "application/java-serialized-object": { "source": "apache", "compressible": false, "extensions": ["ser"] },
  "application/java-vm": { "source": "apache", "compressible": false, "extensions": ["class"] },
  "application/javascript": { "source": "iana", "charset": "UTF-8", "compressible": true, "extensions": ["js", "mjs"] },
  "application/jf2feed+json": { "source": "iana", "compressible": true },
  "application/jose": { "source": "iana" },
  "application/jose+json": { "source": "iana", "compressible": true },
  "application/jrd+json": { "source": "iana", "compressible": true },
  "application/jscalendar+json": { "source": "iana", "compressible": true },
  "application/json": { "source": "iana", "charset": "UTF-8", "compressible": true, "extensions": ["json", "map"] },
  "application/json-patch+json": { "source": "iana", "compressible": true },
  "application/json-seq": { "source": "iana" },
  "application/json5": { "extensions": ["json5"] },
  "application/jsonml+json": { "source": "apache", "compressible": true, "extensions": ["jsonml"] },
  "application/jwk+json": { "source": "iana", "compressible": true },
  "application/jwk-set+json": { "source": "iana", "compressible": true },
  "application/jwt": { "source": "iana" },
  "application/kpml-request+xml": { "source": "iana", "compressible": true },
  "application/kpml-response+xml": { "source": "iana", "compressible": true },
  "application/ld+json": { "source": "iana", "compressible": true, "extensions": ["jsonld"] },
  "application/lgr+xml": { "source": "iana", "compressible": true, "extensions": ["lgr"] },
  "application/link-format": { "source": "iana" },
  "application/load-control+xml": { "source": "iana", "compressible": true },
  "application/lost+xml": { "source": "iana", "compressible": true, "extensions": ["lostxml"] },
  "application/lostsync+xml": { "source": "iana", "compressible": true },
  "application/lpf+zip": { "source": "iana", "compressible": false },
  "application/lxf": { "source": "iana" },
  "application/mac-binhex40": { "source": "iana", "extensions": ["hqx"] },
  "application/mac-compactpro": { "source": "apache", "extensions": ["cpt"] },
  "application/macwriteii": { "source": "iana" },
  "application/mads+xml": { "source": "iana", "compressible": true, "extensions": ["mads"] },
  "application/manifest+json": { "source": "iana", "charset": "UTF-8", "compressible": true, "extensions": ["webmanifest"] },
  "application/marc": { "source": "iana", "extensions": ["mrc"] },
  "application/marcxml+xml": { "source": "iana", "compressible": true, "extensions": ["mrcx"] },
  "application/mathematica": { "source": "iana", "extensions": ["ma", "nb", "mb"] },
  "application/mathml+xml": { "source": "iana", "compressible": true, "extensions": ["mathml"] },
  "application/mathml-content+xml": { "source": "iana", "compressible": true },
  "application/mathml-presentation+xml": { "source": "iana", "compressible": true },
  "application/mbms-associated-procedure-description+xml": { "source": "iana", "compressible": true },
  "application/mbms-deregister+xml": { "source": "iana", "compressible": true },
  "application/mbms-envelope+xml": { "source": "iana", "compressible": true },
  "application/mbms-msk+xml": { "source": "iana", "compressible": true },
  "application/mbms-msk-response+xml": { "source": "iana", "compressible": true },
  "application/mbms-protection-description+xml": { "source": "iana", "compressible": true },
  "application/mbms-reception-report+xml": { "source": "iana", "compressible": true },
  "application/mbms-register+xml": { "source": "iana", "compressible": true },
  "application/mbms-register-response+xml": { "source": "iana", "compressible": true },
  "application/mbms-schedule+xml": { "source": "iana", "compressible": true },
  "application/mbms-user-service-description+xml": { "source": "iana", "compressible": true },
  "application/mbox": { "source": "iana", "extensions": ["mbox"] },
  "application/media-policy-dataset+xml": { "source": "iana", "compressible": true, "extensions": ["mpf"] },
  "application/media_control+xml": { "source": "iana", "compressible": true },
  "application/mediaservercontrol+xml": { "source": "iana", "compressible": true, "extensions": ["mscml"] },
  "application/merge-patch+json": { "source": "iana", "compressible": true },
  "application/metalink+xml": { "source": "apache", "compressible": true, "extensions": ["metalink"] },
  "application/metalink4+xml": { "source": "iana", "compressible": true, "extensions": ["meta4"] },
  "application/mets+xml": { "source": "iana", "compressible": true, "extensions": ["mets"] },
  "application/mf4": { "source": "iana" },
  "application/mikey": { "source": "iana" },
  "application/mipc": { "source": "iana" },
  "application/missing-blocks+cbor-seq": { "source": "iana" },
  "application/mmt-aei+xml": { "source": "iana", "compressible": true, "extensions": ["maei"] },
  "application/mmt-usd+xml": { "source": "iana", "compressible": true, "extensions": ["musd"] },
  "application/mods+xml": { "source": "iana", "compressible": true, "extensions": ["mods"] },
  "application/moss-keys": { "source": "iana" },
  "application/moss-signature": { "source": "iana" },
  "application/mosskey-data": { "source": "iana" },
  "application/mosskey-request": { "source": "iana" },
  "application/mp21": { "source": "iana", "extensions": ["m21", "mp21"] },
  "application/mp4": { "source": "iana", "extensions": ["mp4s", "m4p"] },
  "application/mpeg4-generic": { "source": "iana" },
  "application/mpeg4-iod": { "source": "iana" },
  "application/mpeg4-iod-xmt": { "source": "iana" },
  "application/mrb-consumer+xml": { "source": "iana", "compressible": true },
  "application/mrb-publish+xml": { "source": "iana", "compressible": true },
  "application/msc-ivr+xml": { "source": "iana", "charset": "UTF-8", "compressible": true },
  "application/msc-mixer+xml": { "source": "iana", "charset": "UTF-8", "compressible": true },
  "application/msword": { "source": "iana", "compressible": false, "extensions": ["doc", "dot"] },
  "application/mud+json": { "source": "iana", "compressible": true },
  "application/multipart-core": { "source": "iana" },
  "application/mxf": { "source": "iana", "extensions": ["mxf"] },
  "application/n-quads": { "source": "iana", "extensions": ["nq"] },
  "application/n-triples": { "source": "iana", "extensions": ["nt"] },
  "application/nasdata": { "source": "iana" },
  "application/news-checkgroups": { "source": "iana", "charset": "US-ASCII" },
  "application/news-groupinfo": { "source": "iana", "charset": "US-ASCII" },
  "application/news-transmission": { "source": "iana" },
  "application/nlsml+xml": { "source": "iana", "compressible": true },
  "application/node": { "source": "iana", "extensions": ["cjs"] },
  "application/nss": { "source": "iana" },
  "application/oauth-authz-req+jwt": { "source": "iana" },
  "application/oblivious-dns-message": { "source": "iana" },
  "application/ocsp-request": { "source": "iana" },
  "application/ocsp-response": { "source": "iana" },
  "application/octet-stream": { "source": "iana", "compressible": false, "extensions": ["bin", "dms", "lrf", "mar", "so", "dist", "distz", "pkg", "bpk", "dump", "elc", "deploy", "exe", "dll", "deb", "dmg", "iso", "img", "msi", "msp", "msm", "buffer"] },
  "application/oda": { "source": "iana", "extensions": ["oda"] },
  "application/odm+xml": { "source": "iana", "compressible": true },
  "application/odx": { "source": "iana" },
  "application/oebps-package+xml": { "source": "iana", "compressible": true, "extensions": ["opf"] },
  "application/ogg": { "source": "iana", "compressible": false, "extensions": ["ogx"] },
  "application/omdoc+xml": { "source": "apache", "compressible": true, "extensions": ["omdoc"] },
  "application/onenote": { "source": "apache", "extensions": ["onetoc", "onetoc2", "onetmp", "onepkg"] },
  "application/opc-nodeset+xml": { "source": "iana", "compressible": true },
  "application/oscore": { "source": "iana" },
  "application/oxps": { "source": "iana", "extensions": ["oxps"] },
  "application/p21": { "source": "iana" },
  "application/p21+zip": { "source": "iana", "compressible": false },
  "application/p2p-overlay+xml": { "source": "iana", "compressible": true, "extensions": ["relo"] },
  "application/parityfec": { "source": "iana" },
  "application/passport": { "source": "iana" },
  "application/patch-ops-error+xml": { "source": "iana", "compressible": true, "extensions": ["xer"] },
  "application/pdf": { "source": "iana", "compressible": false, "extensions": ["pdf"] },
  "application/pdx": { "source": "iana" },
  "application/pem-certificate-chain": { "source": "iana" },
  "application/pgp-encrypted": { "source": "iana", "compressible": false, "extensions": ["pgp"] },
  "application/pgp-keys": { "source": "iana", "extensions": ["asc"] },
  "application/pgp-signature": { "source": "iana", "extensions": ["asc", "sig"] },
  "application/pics-rules": { "source": "apache", "extensions": ["prf"] },
  "application/pidf+xml": { "source": "iana", "charset": "UTF-8", "compressible": true },
  "application/pidf-diff+xml": { "source": "iana", "charset": "UTF-8", "compressible": true },
  "application/pkcs10": { "source": "iana", "extensions": ["p10"] },
  "application/pkcs12": { "source": "iana" },
  "application/pkcs7-mime": { "source": "iana", "extensions": ["p7m", "p7c"] },
  "application/pkcs7-signature": { "source": "iana", "extensions": ["p7s"] },
  "application/pkcs8": { "source": "iana", "extensions": ["p8"] },
  "application/pkcs8-encrypted": { "source": "iana" },
  "application/pkix-attr-cert": { "source": "iana", "extensions": ["ac"] },
  "application/pkix-cert": { "source": "iana", "extensions": ["cer"] },
  "application/pkix-crl": { "source": "iana", "extensions": ["crl"] },
  "application/pkix-pkipath": { "source": "iana", "extensions": ["pkipath"] },
  "application/pkixcmp": { "source": "iana", "extensions": ["pki"] },
  "application/pls+xml": { "source": "iana", "compressible": true, "extensions": ["pls"] },
  "application/poc-settings+xml": { "source": "iana", "charset": "UTF-8", "compressible": true },
  "application/postscript": { "source": "iana", "compressible": true, "extensions": ["ai", "eps", "ps"] },
  "application/ppsp-tracker+json": { "source": "iana", "compressible": true },
  "application/problem+json": { "source": "iana", "compressible": true },
  "application/problem+xml": { "source": "iana", "compressible": true },
  "application/provenance+xml": { "source": "iana", "compressible": true, "extensions": ["provx"] },
  "application/prs.alvestrand.titrax-sheet": { "source": "iana" },
  "application/prs.cww": { "source": "iana", "extensions": ["cww"] },
  "application/prs.cyn": { "source": "iana", "charset": "7-BIT" },
  "application/prs.hpub+zip": { "source": "iana", "compressible": false },
  "application/prs.nprend": { "source": "iana" },
  "application/prs.plucker": { "source": "iana" },
  "application/prs.rdf-xml-crypt": { "source": "iana" },
  "application/prs.xsf+xml": { "source": "iana", "compressible": true },
  "application/pskc+xml": { "source": "iana", "compressible": true, "extensions": ["pskcxml"] },
  "application/pvd+json": { "source": "iana", "compressible": true },
  "application/qsig": { "source": "iana" },
  "application/raml+yaml": { "compressible": true, "extensions": ["raml"] },
  "application/raptorfec": { "source": "iana" },
  "application/rdap+json": { "source": "iana", "compressible": true },
  "application/rdf+xml": { "source": "iana", "compressible": true, "extensions": ["rdf", "owl"] },
  "application/reginfo+xml": { "source": "iana", "compressible": true, "extensions": ["rif"] },
  "application/relax-ng-compact-syntax": { "source": "iana", "extensions": ["rnc"] },
  "application/remote-printing": { "source": "iana" },
  "application/reputon+json": { "source": "iana", "compressible": true },
  "application/resource-lists+xml": { "source": "iana", "compressible": true, "extensions": ["rl"] },
  "application/resource-lists-diff+xml": { "source": "iana", "compressible": true, "extensions": ["rld"] },
  "application/rfc+xml": { "source": "iana", "compressible": true },
  "application/riscos": { "source": "iana" },
  "application/rlmi+xml": { "source": "iana", "compressible": true },
  "application/rls-services+xml": { "source": "iana", "compressible": true, "extensions": ["rs"] },
  "application/route-apd+xml": { "source": "iana", "compressible": true, "extensions": ["rapd"] },
  "application/route-s-tsid+xml": { "source": "iana", "compressible": true, "extensions": ["sls"] },
  "application/route-usd+xml": { "source": "iana", "compressible": true, "extensions": ["rusd"] },
  "application/rpki-ghostbusters": { "source": "iana", "extensions": ["gbr"] },
  "application/rpki-manifest": { "source": "iana", "extensions": ["mft"] },
  "application/rpki-publication": { "source": "iana" },
  "application/rpki-roa": { "source": "iana", "extensions": ["roa"] },
  "application/rpki-updown": { "source": "iana" },
  "application/rsd+xml": { "source": "apache", "compressible": true, "extensions": ["rsd"] },
  "application/rss+xml": { "source": "apache", "compressible": true, "extensions": ["rss"] },
  "application/rtf": { "source": "iana", "compressible": true, "extensions": ["rtf"] },
  "application/rtploopback": { "source": "iana" },
  "application/rtx": { "source": "iana" },
  "application/samlassertion+xml": { "source": "iana", "compressible": true },
  "application/samlmetadata+xml": { "source": "iana", "compressible": true },
  "application/sarif+json": { "source": "iana", "compressible": true },
  "application/sarif-external-properties+json": { "source": "iana", "compressible": true },
  "application/sbe": { "source": "iana" },
  "application/sbml+xml": { "source": "iana", "compressible": true, "extensions": ["sbml"] },
  "application/scaip+xml": { "source": "iana", "compressible": true },
  "application/scim+json": { "source": "iana", "compressible": true },
  "application/scvp-cv-request": { "source": "iana", "extensions": ["scq"] },
  "application/scvp-cv-response": { "source": "iana", "extensions": ["scs"] },
  "application/scvp-vp-request": { "source": "iana", "extensions": ["spq"] },
  "application/scvp-vp-response": { "source": "iana", "extensions": ["spp"] },
  "application/sdp": { "source": "iana", "extensions": ["sdp"] },
  "application/secevent+jwt": { "source": "iana" },
  "application/senml+cbor": { "source": "iana" },
  "application/senml+json": { "source": "iana", "compressible": true },
  "application/senml+xml": { "source": "iana", "compressible": true, "extensions": ["senmlx"] },
  "application/senml-etch+cbor": { "source": "iana" },
  "application/senml-etch+json": { "source": "iana", "compressible": true },
  "application/senml-exi": { "source": "iana" },
  "application/sensml+cbor": { "source": "iana" },
  "application/sensml+json": { "source": "iana", "compressible": true },
  "application/sensml+xml": { "source": "iana", "compressible": true, "extensions": ["sensmlx"] },
  "application/sensml-exi": { "source": "iana" },
  "application/sep+xml": { "source": "iana", "compressible": true },
  "application/sep-exi": { "source": "iana" },
  "application/session-info": { "source": "iana" },
  "application/set-payment": { "source": "iana" },
  "application/set-payment-initiation": { "source": "iana", "extensions": ["setpay"] },
  "application/set-registration": { "source": "iana" },
  "application/set-registration-initiation": { "source": "iana", "extensions": ["setreg"] },
  "application/sgml": { "source": "iana" },
  "application/sgml-open-catalog": { "source": "iana" },
  "application/shf+xml": { "source": "iana", "compressible": true, "extensions": ["shf"] },
  "application/sieve": { "source": "iana", "extensions": ["siv", "sieve"] },
  "application/simple-filter+xml": { "source": "iana", "compressible": true },
  "application/simple-message-summary": { "source": "iana" },
  "application/simplesymbolcontainer": { "source": "iana" },
  "application/sipc": { "source": "iana" },
  "application/slate": { "source": "iana" },
  "application/smil": { "source": "iana" },
  "application/smil+xml": { "source": "iana", "compressible": true, "extensions": ["smi", "smil"] },
  "application/smpte336m": { "source": "iana" },
  "application/soap+fastinfoset": { "source": "iana" },
  "application/soap+xml": { "source": "iana", "compressible": true },
  "application/sparql-query": { "source": "iana", "extensions": ["rq"] },
  "application/sparql-results+xml": { "source": "iana", "compressible": true, "extensions": ["srx"] },
  "application/spdx+json": { "source": "iana", "compressible": true },
  "application/spirits-event+xml": { "source": "iana", "compressible": true },
  "application/sql": { "source": "iana" },
  "application/srgs": { "source": "iana", "extensions": ["gram"] },
  "application/srgs+xml": { "source": "iana", "compressible": true, "extensions": ["grxml"] },
  "application/sru+xml": { "source": "iana", "compressible": true, "extensions": ["sru"] },
  "application/ssdl+xml": { "source": "apache", "compressible": true, "extensions": ["ssdl"] },
  "application/ssml+xml": { "source": "iana", "compressible": true, "extensions": ["ssml"] },
  "application/stix+json": { "source": "iana", "compressible": true },
  "application/swid+xml": { "source": "iana", "compressible": true, "extensions": ["swidtag"] },
  "application/tamp-apex-update": { "source": "iana" },
  "application/tamp-apex-update-confirm": { "source": "iana" },
  "application/tamp-community-update": { "source": "iana" },
  "application/tamp-community-update-confirm": { "source": "iana" },
  "application/tamp-error": { "source": "iana" },
  "application/tamp-sequence-adjust": { "source": "iana" },
  "application/tamp-sequence-adjust-confirm": { "source": "iana" },
  "application/tamp-status-query": { "source": "iana" },
  "application/tamp-status-response": { "source": "iana" },
  "application/tamp-update": { "source": "iana" },
  "application/tamp-update-confirm": { "source": "iana" },
  "application/tar": { "compressible": true },
  "application/taxii+json": { "source": "iana", "compressible": true },
  "application/td+json": { "source": "iana", "compressible": true },
  "application/tei+xml": { "source": "iana", "compressible": true, "extensions": ["tei", "teicorpus"] },
  "application/tetra_isi": { "source": "iana" },
  "application/thraud+xml": { "source": "iana", "compressible": true, "extensions": ["tfi"] },
  "application/timestamp-query": { "source": "iana" },
  "application/timestamp-reply": { "source": "iana" },
  "application/timestamped-data": { "source": "iana", "extensions": ["tsd"] },
  "application/tlsrpt+gzip": { "source": "iana" },
  "application/tlsrpt+json": { "source": "iana", "compressible": true },
  "application/tnauthlist": { "source": "iana" },
  "application/token-introspection+jwt": { "source": "iana" },
  "application/toml": { "compressible": true, "extensions": ["toml"] },
  "application/trickle-ice-sdpfrag": { "source": "iana" },
  "application/trig": { "source": "iana", "extensions": ["trig"] },
  "application/ttml+xml": { "source": "iana", "compressible": true, "extensions": ["ttml"] },
  "application/tve-trigger": { "source": "iana" },
  "application/tzif": { "source": "iana" },
  "application/tzif-leap": { "source": "iana" },
  "application/ubjson": { "compressible": false, "extensions": ["ubj"] },
  "application/ulpfec": { "source": "iana" },
  "application/urc-grpsheet+xml": { "source": "iana", "compressible": true },
  "application/urc-ressheet+xml": { "source": "iana", "compressible": true, "extensions": ["rsheet"] },
  "application/urc-targetdesc+xml": { "source": "iana", "compressible": true, "extensions": ["td"] },
  "application/urc-uisocketdesc+xml": { "source": "iana", "compressible": true },
  "application/vcard+json": { "source": "iana", "compressible": true },
  "application/vcard+xml": { "source": "iana", "compressible": true },
  "application/vemmi": { "source": "iana" },
  "application/vividence.scriptfile": { "source": "apache" },
  "application/vnd.1000minds.decision-model+xml": { "source": "iana", "compressible": true, "extensions": ["1km"] },
  "application/vnd.3gpp-prose+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp-prose-pc3ch+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp-v2x-local-service-information": { "source": "iana" },
  "application/vnd.3gpp.5gnas": { "source": "iana" },
  "application/vnd.3gpp.access-transfer-events+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.bsf+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.gmop+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.gtpc": { "source": "iana" },
  "application/vnd.3gpp.interworking-data": { "source": "iana" },
  "application/vnd.3gpp.lpp": { "source": "iana" },
  "application/vnd.3gpp.mc-signalling-ear": { "source": "iana" },
  "application/vnd.3gpp.mcdata-affiliation-command+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcdata-info+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcdata-payload": { "source": "iana" },
  "application/vnd.3gpp.mcdata-service-config+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcdata-signalling": { "source": "iana" },
  "application/vnd.3gpp.mcdata-ue-config+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcdata-user-profile+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcptt-affiliation-command+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcptt-floor-request+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcptt-info+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcptt-location-info+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcptt-mbms-usage-info+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcptt-service-config+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcptt-signed+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcptt-ue-config+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcptt-ue-init-config+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcptt-user-profile+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcvideo-affiliation-command+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcvideo-affiliation-info+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcvideo-info+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcvideo-location-info+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcvideo-mbms-usage-info+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcvideo-service-config+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcvideo-transmission-request+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcvideo-ue-config+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mcvideo-user-profile+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.mid-call+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.ngap": { "source": "iana" },
  "application/vnd.3gpp.pfcp": { "source": "iana" },
  "application/vnd.3gpp.pic-bw-large": { "source": "iana", "extensions": ["plb"] },
  "application/vnd.3gpp.pic-bw-small": { "source": "iana", "extensions": ["psb"] },
  "application/vnd.3gpp.pic-bw-var": { "source": "iana", "extensions": ["pvb"] },
  "application/vnd.3gpp.s1ap": { "source": "iana" },
  "application/vnd.3gpp.sms": { "source": "iana" },
  "application/vnd.3gpp.sms+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.srvcc-ext+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.srvcc-info+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.state-and-event-info+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp.ussd+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp2.bcmcsinfo+xml": { "source": "iana", "compressible": true },
  "application/vnd.3gpp2.sms": { "source": "iana" },
  "application/vnd.3gpp2.tcap": { "source": "iana", "extensions": ["tcap"] },
  "application/vnd.3lightssoftware.imagescal": { "source": "iana" },
  "application/vnd.3m.post-it-notes": { "source": "iana", "extensions": ["pwn"] },
  "application/vnd.accpac.simply.aso": { "source": "iana", "extensions": ["aso"] },
  "application/vnd.accpac.simply.imp": { "source": "iana", "extensions": ["imp"] },
  "application/vnd.acucobol": { "source": "iana", "extensions": ["acu"] },
  "application/vnd.acucorp": { "source": "iana", "extensions": ["atc", "acutc"] },
  "application/vnd.adobe.air-application-installer-package+zip": { "source": "apache", "compressible": false, "extensions": ["air"] },
  "application/vnd.adobe.flash.movie": { "source": "iana" },
  "application/vnd.adobe.formscentral.fcdt": { "source": "iana", "extensions": ["fcdt"] },
  "application/vnd.adobe.fxp": { "source": "iana", "extensions": ["fxp", "fxpl"] },
  "application/vnd.adobe.partial-upload": { "source": "iana" },
  "application/vnd.adobe.xdp+xml": { "source": "iana", "compressible": true, "extensions": ["xdp"] },
  "application/vnd.adobe.xfdf": { "source": "iana", "extensions": ["xfdf"] },
  "application/vnd.aether.imp": { "source": "iana" },
  "application/vnd.afpc.afplinedata": { "source": "iana" },
  "application/vnd.afpc.afplinedata-pagedef": { "source": "iana" },
  "application/vnd.afpc.cmoca-cmresource": { "source": "iana" },
  "application/vnd.afpc.foca-charset": { "source": "iana" },
  "application/vnd.afpc.foca-codedfont": { "source": "iana" },
  "application/vnd.afpc.foca-codepage": { "source": "iana" },
  "application/vnd.afpc.modca": { "source": "iana" },
  "application/vnd.afpc.modca-cmtable": { "source": "iana" },
  "application/vnd.afpc.modca-formdef": { "source": "iana" },
  "application/vnd.afpc.modca-mediummap": { "source": "iana" },
  "application/vnd.afpc.modca-objectcontainer": { "source": "iana" },
  "application/vnd.afpc.modca-overlay": { "source": "iana" },
  "application/vnd.afpc.modca-pagesegment": { "source": "iana" },
  "application/vnd.age": { "source": "iana", "extensions": ["age"] },
  "application/vnd.ah-barcode": { "source": "iana" },
  "application/vnd.ahead.space": { "source": "iana", "extensions": ["ahead"] },
  "application/vnd.airzip.filesecure.azf": { "source": "iana", "extensions": ["azf"] },
  "application/vnd.airzip.filesecure.azs": { "source": "iana", "extensions": ["azs"] },
  "application/vnd.amadeus+json": { "source": "iana", "compressible": true },
  "application/vnd.amazon.ebook": { "source": "apache", "extensions": ["azw"] },
  "application/vnd.amazon.mobi8-ebook": { "source": "iana" },
  "application/vnd.americandynamics.acc": { "source": "iana", "extensions": ["acc"] },
  "application/vnd.amiga.ami": { "source": "iana", "extensions": ["ami"] },
  "application/vnd.amundsen.maze+xml": { "source": "iana", "compressible": true },
  "application/vnd.android.ota": { "source": "iana" },
  "application/vnd.android.package-archive": { "source": "apache", "compressible": false, "extensions": ["apk"] },
  "application/vnd.anki": { "source": "iana" },
  "application/vnd.anser-web-certificate-issue-initiation": { "source": "iana", "extensions": ["cii"] },
  "application/vnd.anser-web-funds-transfer-initiation": { "source": "apache", "extensions": ["fti"] },
  "application/vnd.antix.game-component": { "source": "iana", "extensions": ["atx"] },
  "application/vnd.apache.arrow.file": { "source": "iana" },
  "application/vnd.apache.arrow.stream": { "source": "iana" },
  "application/vnd.apache.thrift.binary": { "source": "iana" },
  "application/vnd.apache.thrift.compact": { "source": "iana" },
  "application/vnd.apache.thrift.json": { "source": "iana" },
  "application/vnd.api+json": { "source": "iana", "compressible": true },
  "application/vnd.aplextor.warrp+json": { "source": "iana", "compressible": true },
  "application/vnd.apothekende.reservation+json": { "source": "iana", "compressible": true },
  "application/vnd.apple.installer+xml": { "source": "iana", "compressible": true, "extensions": ["mpkg"] },
  "application/vnd.apple.keynote": { "source": "iana", "extensions": ["key"] },
  "application/vnd.apple.mpegurl": { "source": "iana", "extensions": ["m3u8"] },
  "application/vnd.apple.numbers": { "source": "iana", "extensions": ["numbers"] },
  "application/vnd.apple.pages": { "source": "iana", "extensions": ["pages"] },
  "application/vnd.apple.pkpass": { "compressible": false, "extensions": ["pkpass"] },
  "application/vnd.arastra.swi": { "source": "iana" },
  "application/vnd.aristanetworks.swi": { "source": "iana", "extensions": ["swi"] },
  "application/vnd.artisan+json": { "source": "iana", "compressible": true },
  "application/vnd.artsquare": { "source": "iana" },
  "application/vnd.astraea-software.iota": { "source": "iana", "extensions": ["iota"] },
  "application/vnd.audiograph": { "source": "iana", "extensions": ["aep"] },
  "application/vnd.autopackage": { "source": "iana" },
  "application/vnd.avalon+json": { "source": "iana", "compressible": true },
  "application/vnd.avistar+xml": { "source": "iana", "compressible": true },
  "application/vnd.balsamiq.bmml+xml": { "source": "iana", "compressible": true, "extensions": ["bmml"] },
  "application/vnd.balsamiq.bmpr": { "source": "iana" },
  "application/vnd.banana-accounting": { "source": "iana" },
  "application/vnd.bbf.usp.error": { "source": "iana" },
  "application/vnd.bbf.usp.msg": { "source": "iana" },
  "application/vnd.bbf.usp.msg+json": { "source": "iana", "compressible": true },
  "application/vnd.bekitzur-stech+json": { "source": "iana", "compressible": true },
  "application/vnd.bint.med-content": { "source": "iana" },
  "application/vnd.biopax.rdf+xml": { "source": "iana", "compressible": true },
  "application/vnd.blink-idb-value-wrapper": { "source": "iana" },
  "application/vnd.blueice.multipass": { "source": "iana", "extensions": ["mpm"] },
  "application/vnd.bluetooth.ep.oob": { "source": "iana" },
  "application/vnd.bluetooth.le.oob": { "source": "iana" },
  "application/vnd.bmi": { "source": "iana", "extensions": ["bmi"] },
  "application/vnd.bpf": { "source": "iana" },
  "application/vnd.bpf3": { "source": "iana" },
  "application/vnd.businessobjects": { "source": "iana", "extensions": ["rep"] },
  "application/vnd.byu.uapi+json": { "source": "iana", "compressible": true },
  "application/vnd.cab-jscript": { "source": "iana" },
  "application/vnd.canon-cpdl": { "source": "iana" },
  "application/vnd.canon-lips": { "source": "iana" },
  "application/vnd.capasystems-pg+json": { "source": "iana", "compressible": true },
  "application/vnd.cendio.thinlinc.clientconf": { "source": "iana" },
  "application/vnd.century-systems.tcp_stream": { "source": "iana" },
  "application/vnd.chemdraw+xml": { "source": "iana", "compressible": true, "extensions": ["cdxml"] },
  "application/vnd.chess-pgn": { "source": "iana" },
  "application/vnd.chipnuts.karaoke-mmd": { "source": "iana", "extensions": ["mmd"] },
  "application/vnd.ciedi": { "source": "iana" },
  "application/vnd.cinderella": { "source": "iana", "extensions": ["cdy"] },
  "application/vnd.cirpack.isdn-ext": { "source": "iana" },
  "application/vnd.citationstyles.style+xml": { "source": "iana", "compressible": true, "extensions": ["csl"] },
  "application/vnd.claymore": { "source": "iana", "extensions": ["cla"] },
  "application/vnd.cloanto.rp9": { "source": "iana", "extensions": ["rp9"] },
  "application/vnd.clonk.c4group": { "source": "iana", "extensions": ["c4g", "c4d", "c4f", "c4p", "c4u"] },
  "application/vnd.cluetrust.cartomobile-config": { "source": "iana", "extensions": ["c11amc"] },
  "application/vnd.cluetrust.cartomobile-config-pkg": { "source": "iana", "extensions": ["c11amz"] },
  "application/vnd.coffeescript": { "source": "iana" },
  "application/vnd.collabio.xodocuments.document": { "source": "iana" },
  "application/vnd.collabio.xodocuments.document-template": { "source": "iana" },
  "application/vnd.collabio.xodocuments.presentation": { "source": "iana" },
  "application/vnd.collabio.xodocuments.presentation-template": { "source": "iana" },
  "application/vnd.collabio.xodocuments.spreadsheet": { "source": "iana" },
  "application/vnd.collabio.xodocuments.spreadsheet-template": { "source": "iana" },
  "application/vnd.collection+json": { "source": "iana", "compressible": true },
  "application/vnd.collection.doc+json": { "source": "iana", "compressible": true },
  "application/vnd.collection.next+json": { "source": "iana", "compressible": true },
  "application/vnd.comicbook+zip": { "source": "iana", "compressible": false },
  "application/vnd.comicbook-rar": { "source": "iana" },
  "application/vnd.commerce-battelle": { "source": "iana" },
  "application/vnd.commonspace": { "source": "iana", "extensions": ["csp"] },
  "application/vnd.contact.cmsg": { "source": "iana", "extensions": ["cdbcmsg"] },
  "application/vnd.coreos.ignition+json": { "source": "iana", "compressible": true },
  "application/vnd.cosmocaller": { "source": "iana", "extensions": ["cmc"] },
  "application/vnd.crick.clicker": { "source": "iana", "extensions": ["clkx"] },
  "application/vnd.crick.clicker.keyboard": { "source": "iana", "extensions": ["clkk"] },
  "application/vnd.crick.clicker.palette": { "source": "iana", "extensions": ["clkp"] },
  "application/vnd.crick.clicker.template": { "source": "iana", "extensions": ["clkt"] },
  "application/vnd.crick.clicker.wordbank": { "source": "iana", "extensions": ["clkw"] },
  "application/vnd.criticaltools.wbs+xml": { "source": "iana", "compressible": true, "extensions": ["wbs"] },
  "application/vnd.cryptii.pipe+json": { "source": "iana", "compressible": true },
  "application/vnd.crypto-shade-file": { "source": "iana" },
  "application/vnd.cryptomator.encrypted": { "source": "iana" },
  "application/vnd.cryptomator.vault": { "source": "iana" },
  "application/vnd.ctc-posml": { "source": "iana", "extensions": ["pml"] },
  "application/vnd.ctct.ws+xml": { "source": "iana", "compressible": true },
  "application/vnd.cups-pdf": { "source": "iana" },
  "application/vnd.cups-postscript": { "source": "iana" },
  "application/vnd.cups-ppd": { "source": "iana", "extensions": ["ppd"] },
  "application/vnd.cups-raster": { "source": "iana" },
  "application/vnd.cups-raw": { "source": "iana" },
  "application/vnd.curl": { "source": "iana" },
  "application/vnd.curl.car": { "source": "apache", "extensions": ["car"] },
  "application/vnd.curl.pcurl": { "source": "apache", "extensions": ["pcurl"] },
  "application/vnd.cyan.dean.root+xml": { "source": "iana", "compressible": true },
  "application/vnd.cybank": { "source": "iana" },
  "application/vnd.cyclonedx+json": { "source": "iana", "compressible": true },
  "application/vnd.cyclonedx+xml": { "source": "iana", "compressible": true },
  "application/vnd.d2l.coursepackage1p0+zip": { "source": "iana", "compressible": false },
  "application/vnd.d3m-dataset": { "source": "iana" },
  "application/vnd.d3m-problem": { "source": "iana" },
  "application/vnd.dart": { "source": "iana", "compressible": true, "extensions": ["dart"] },
  "application/vnd.data-vision.rdz": { "source": "iana", "extensions": ["rdz"] },
  "application/vnd.datapackage+json": { "source": "iana", "compressible": true },
  "application/vnd.dataresource+json": { "source": "iana", "compressible": true },
  "application/vnd.dbf": { "source": "iana", "extensions": ["dbf"] },
  "application/vnd.debian.binary-package": { "source": "iana" },
  "application/vnd.dece.data": { "source": "iana", "extensions": ["uvf", "uvvf", "uvd", "uvvd"] },
  "application/vnd.dece.ttml+xml": { "source": "iana", "compressible": true, "extensions": ["uvt", "uvvt"] },
  "application/vnd.dece.unspecified": { "source": "iana", "extensions": ["uvx", "uvvx"] },
  "application/vnd.dece.zip": { "source": "iana", "extensions": ["uvz", "uvvz"] },
  "application/vnd.denovo.fcselayout-link": { "source": "iana", "extensions": ["fe_launch"] },
  "application/vnd.desmume.movie": { "source": "iana" },
  "application/vnd.dir-bi.plate-dl-nosuffix": { "source": "iana" },
  "application/vnd.dm.delegation+xml": { "source": "iana", "compressible": true },
  "application/vnd.dna": { "source": "iana", "extensions": ["dna"] },
  "application/vnd.document+json": { "source": "iana", "compressible": true },
  "application/vnd.dolby.mlp": { "source": "apache", "extensions": ["mlp"] },
  "application/vnd.dolby.mobile.1": { "source": "iana" },
  "application/vnd.dolby.mobile.2": { "source": "iana" },
  "application/vnd.doremir.scorecloud-binary-document": { "source": "iana" },
  "application/vnd.dpgraph": { "source": "iana", "extensions": ["dpg"] },
  "application/vnd.dreamfactory": { "source": "iana", "extensions": ["dfac"] },
  "application/vnd.drive+json": { "source": "iana", "compressible": true },
  "application/vnd.ds-keypoint": { "source": "apache", "extensions": ["kpxx"] },
  "application/vnd.dtg.local": { "source": "iana" },
  "application/vnd.dtg.local.flash": { "source": "iana" },
  "application/vnd.dtg.local.html": { "source": "iana" },
  "application/vnd.dvb.ait": { "source": "iana", "extensions": ["ait"] },
  "application/vnd.dvb.dvbisl+xml": { "source": "iana", "compressible": true },
  "application/vnd.dvb.dvbj": { "source": "iana" },
  "application/vnd.dvb.esgcontainer": { "source": "iana" },
  "application/vnd.dvb.ipdcdftnotifaccess": { "source": "iana" },
  "application/vnd.dvb.ipdcesgaccess": { "source": "iana" },
  "application/vnd.dvb.ipdcesgaccess2": { "source": "iana" },
  "application/vnd.dvb.ipdcesgpdd": { "source": "iana" },
  "application/vnd.dvb.ipdcroaming": { "source": "iana" },
  "application/vnd.dvb.iptv.alfec-base": { "source": "iana" },
  "application/vnd.dvb.iptv.alfec-enhancement": { "source": "iana" },
  "application/vnd.dvb.notif-aggregate-root+xml": { "source": "iana", "compressible": true },
  "application/vnd.dvb.notif-container+xml": { "source": "iana", "compressible": true },
  "application/vnd.dvb.notif-generic+xml": { "source": "iana", "compressible": true },
  "application/vnd.dvb.notif-ia-msglist+xml": { "source": "iana", "compressible": true },
  "application/vnd.dvb.notif-ia-registration-request+xml": { "source": "iana", "compressible": true },
  "application/vnd.dvb.notif-ia-registration-response+xml": { "source": "iana", "compressible": true },
  "application/vnd.dvb.notif-init+xml": { "source": "iana", "compressible": true },
  "application/vnd.dvb.pfr": { "source": "iana" },
  "application/vnd.dvb.service": { "source": "iana", "extensions": ["svc"] },
  "application/vnd.dxr": { "source": "iana" },
  "application/vnd.dynageo": { "source": "iana", "extensions": ["geo"] },
  "application/vnd.dzr": { "source": "iana" },
  "application/vnd.easykaraoke.cdgdownload": { "source": "iana" },
  "application/vnd.ecdis-update": { "source": "iana" },
  "application/vnd.ecip.rlp": { "source": "iana" },
  "application/vnd.eclipse.ditto+json": { "source": "iana", "compressible": true },
  "application/vnd.ecowin.chart": { "source": "iana", "extensions": ["mag"] },
  "application/vnd.ecowin.filerequest": { "source": "iana" },
  "application/vnd.ecowin.fileupdate": { "source": "iana" },
  "application/vnd.ecowin.series": { "source": "iana" },
  "application/vnd.ecowin.seriesrequest": { "source": "iana" },
  "application/vnd.ecowin.seriesupdate": { "source": "iana" },
  "application/vnd.efi.img": { "source": "iana" },
  "application/vnd.efi.iso": { "source": "iana" },
  "application/vnd.emclient.accessrequest+xml": { "source": "iana", "compressible": true },
  "application/vnd.enliven": { "source": "iana", "extensions": ["nml"] },
  "application/vnd.enphase.envoy": { "source": "iana" },
  "application/vnd.eprints.data+xml": { "source": "iana", "compressible": true },
  "application/vnd.epson.esf": { "source": "iana", "extensions": ["esf"] },
  "application/vnd.epson.msf": { "source": "iana", "extensions": ["msf"] },
  "application/vnd.epson.quickanime": { "source": "iana", "extensions": ["qam"] },
  "application/vnd.epson.salt": { "source": "iana", "extensions": ["slt"] },
  "application/vnd.epson.ssf": { "source": "iana", "extensions": ["ssf"] },
  "application/vnd.ericsson.quickcall": { "source": "iana" },
  "application/vnd.espass-espass+zip": { "source": "iana", "compressible": false },
  "application/vnd.eszigno3+xml": { "source": "iana", "compressible": true, "extensions": ["es3", "et3"] },
  "application/vnd.etsi.aoc+xml": { "source": "iana", "compressible": true },
  "application/vnd.etsi.asic-e+zip": { "source": "iana", "compressible": false },
  "application/vnd.etsi.asic-s+zip": { "source": "iana", "compressible": false },
  "application/vnd.etsi.cug+xml": { "source": "iana", "compressible": true },
  "application/vnd.etsi.iptvcommand+xml": { "source": "iana", "compressible": true },
  "application/vnd.etsi.iptvdiscovery+xml": { "source": "iana", "compressible": true },
  "application/vnd.etsi.iptvprofile+xml": { "source": "iana", "compressible": true },
  "application/vnd.etsi.iptvsad-bc+xml": { "source": "iana", "compressible": true },
  "application/vnd.etsi.iptvsad-cod+xml": { "source": "iana", "compressible": true },
  "application/vnd.etsi.iptvsad-npvr+xml": { "source": "iana", "compressible": true },
  "application/vnd.etsi.iptvservice+xml": { "source": "iana", "compressible": true },
  "application/vnd.etsi.iptvsync+xml": { "source": "iana", "compressible": true },
  "application/vnd.etsi.iptvueprofile+xml": { "source": "iana", "compressible": true },
  "application/vnd.etsi.mcid+xml": { "source": "iana", "compressible": true },
  "application/vnd.etsi.mheg5": { "source": "iana" },
  "application/vnd.etsi.overload-control-policy-dataset+xml": { "source": "iana", "compressible": true },
  "application/vnd.etsi.pstn+xml": { "source": "iana", "compressible": true },
  "application/vnd.etsi.sci+xml": { "source": "iana", "compressible": true },
  "application/vnd.etsi.simservs+xml": { "source": "iana", "compressible": true },
  "application/vnd.etsi.timestamp-token": { "source": "iana" },
  "application/vnd.etsi.tsl+xml": { "source": "iana", "compressible": true },
  "application/vnd.etsi.tsl.der": { "source": "iana" },
  "application/vnd.eu.kasparian.car+json": { "source": "iana", "compressible": true },
  "application/vnd.eudora.data": { "source": "iana" },
  "application/vnd.evolv.ecig.profile": { "source": "iana" },
  "application/vnd.evolv.ecig.settings": { "source": "iana" },
  "application/vnd.evolv.ecig.theme": { "source": "iana" },
  "application/vnd.exstream-empower+zip": { "source": "iana", "compressible": false },
  "application/vnd.exstream-package": { "source": "iana" },
  "application/vnd.ezpix-album": { "source": "iana", "extensions": ["ez2"] },
  "application/vnd.ezpix-package": { "source": "iana", "extensions": ["ez3"] },
  "application/vnd.f-secure.mobile": { "source": "iana" },
  "application/vnd.familysearch.gedcom+zip": { "source": "iana", "compressible": false },
  "application/vnd.fastcopy-disk-image": { "source": "iana" },
  "application/vnd.fdf": { "source": "iana", "extensions": ["fdf"] },
  "application/vnd.fdsn.mseed": { "source": "iana", "extensions": ["mseed"] },
  "application/vnd.fdsn.seed": { "source": "iana", "extensions": ["seed", "dataless"] },
  "application/vnd.ffsns": { "source": "iana" },
  "application/vnd.ficlab.flb+zip": { "source": "iana", "compressible": false },
  "application/vnd.filmit.zfc": { "source": "iana" },
  "application/vnd.fints": { "source": "iana" },
  "application/vnd.firemonkeys.cloudcell": { "source": "iana" },
  "application/vnd.flographit": { "source": "iana", "extensions": ["gph"] },
  "application/vnd.fluxtime.clip": { "source": "iana", "extensions": ["ftc"] },
  "application/vnd.font-fontforge-sfd": { "source": "iana" },
  "application/vnd.framemaker": { "source": "iana", "extensions": ["fm", "frame", "maker", "book"] },
  "application/vnd.frogans.fnc": { "source": "iana", "extensions": ["fnc"] },
  "application/vnd.frogans.ltf": { "source": "iana", "extensions": ["ltf"] },
  "application/vnd.fsc.weblaunch": { "source": "iana", "extensions": ["fsc"] },
  "application/vnd.fujifilm.fb.docuworks": { "source": "iana" },
  "application/vnd.fujifilm.fb.docuworks.binder": { "source": "iana" },
  "application/vnd.fujifilm.fb.docuworks.container": { "source": "iana" },
  "application/vnd.fujifilm.fb.jfi+xml": { "source": "iana", "compressible": true },
  "application/vnd.fujitsu.oasys": { "source": "iana", "extensions": ["oas"] },
  "application/vnd.fujitsu.oasys2": { "source": "iana", "extensions": ["oa2"] },
  "application/vnd.fujitsu.oasys3": { "source": "iana", "extensions": ["oa3"] },
  "application/vnd.fujitsu.oasysgp": { "source": "iana", "extensions": ["fg5"] },
  "application/vnd.fujitsu.oasysprs": { "source": "iana", "extensions": ["bh2"] },
  "application/vnd.fujixerox.art-ex": { "source": "iana" },
  "application/vnd.fujixerox.art4": { "source": "iana" },
  "application/vnd.fujixerox.ddd": { "source": "iana", "extensions": ["ddd"] },
  "application/vnd.fujixerox.docuworks": { "source": "iana", "extensions": ["xdw"] },
  "application/vnd.fujixerox.docuworks.binder": { "source": "iana", "extensions": ["xbd"] },
  "application/vnd.fujixerox.docuworks.container": { "source": "iana" },
  "application/vnd.fujixerox.hbpl": { "source": "iana" },
  "application/vnd.fut-misnet": { "source": "iana" },
  "application/vnd.futoin+cbor": { "source": "iana" },
  "application/vnd.futoin+json": { "source": "iana", "compressible": true },
  "application/vnd.fuzzysheet": { "source": "iana", "extensions": ["fzs"] },
  "application/vnd.genomatix.tuxedo": { "source": "iana", "extensions": ["txd"] },
  "application/vnd.gentics.grd+json": { "source": "iana", "compressible": true },
  "application/vnd.geo+json": { "source": "iana", "compressible": true },
  "application/vnd.geocube+xml": { "source": "iana", "compressible": true },
  "application/vnd.geogebra.file": { "source": "iana", "extensions": ["ggb"] },
  "application/vnd.geogebra.slides": { "source": "iana" },
  "application/vnd.geogebra.tool": { "source": "iana", "extensions": ["ggt"] },
  "application/vnd.geometry-explorer": { "source": "iana", "extensions": ["gex", "gre"] },
  "application/vnd.geonext": { "source": "iana", "extensions": ["gxt"] },
  "application/vnd.geoplan": { "source": "iana", "extensions": ["g2w"] },
  "application/vnd.geospace": { "source": "iana", "extensions": ["g3w"] },
  "application/vnd.gerber": { "source": "iana" },
  "application/vnd.globalplatform.card-content-mgt": { "source": "iana" },
  "application/vnd.globalplatform.card-content-mgt-response": { "source": "iana" },
  "application/vnd.gmx": { "source": "iana", "extensions": ["gmx"] },
  "application/vnd.google-apps.document": { "compressible": false, "extensions": ["gdoc"] },
  "application/vnd.google-apps.presentation": { "compressible": false, "extensions": ["gslides"] },
  "application/vnd.google-apps.spreadsheet": { "compressible": false, "extensions": ["gsheet"] },
  "application/vnd.google-earth.kml+xml": { "source": "iana", "compressible": true, "extensions": ["kml"] },
  "application/vnd.google-earth.kmz": { "source": "iana", "compressible": false, "extensions": ["kmz"] },
  "application/vnd.gov.sk.e-form+xml": { "source": "iana", "compressible": true },
  "application/vnd.gov.sk.e-form+zip": { "source": "iana", "compressible": false },
  "application/vnd.gov.sk.xmldatacontainer+xml": { "source": "iana", "compressible": true },
  "application/vnd.grafeq": { "source": "iana", "extensions": ["gqf", "gqs"] },
  "application/vnd.gridmp": { "source": "iana" },
  "application/vnd.groove-account": { "source": "iana", "extensions": ["gac"] },
  "application/vnd.groove-help": { "source": "iana", "extensions": ["ghf"] },
  "application/vnd.groove-identity-message": { "source": "iana", "extensions": ["gim"] },
  "application/vnd.groove-injector": { "source": "iana", "extensions": ["grv"] },
  "application/vnd.groove-tool-message": { "source": "iana", "extensions": ["gtm"] },
  "application/vnd.groove-tool-template": { "source": "iana", "extensions": ["tpl"] },
  "application/vnd.groove-vcard": { "source": "iana", "extensions": ["vcg"] },
  "application/vnd.hal+json": { "source": "iana", "compressible": true },
  "application/vnd.hal+xml": { "source": "iana", "compressible": true, "extensions": ["hal"] },
  "application/vnd.handheld-entertainment+xml": { "source": "iana", "compressible": true, "extensions": ["zmm"] },
  "application/vnd.hbci": { "source": "iana", "extensions": ["hbci"] },
  "application/vnd.hc+json": { "source": "iana", "compressible": true },
  "application/vnd.hcl-bireports": { "source": "iana" },
  "application/vnd.hdt": { "source": "iana" },
  "application/vnd.heroku+json": { "source": "iana", "compressible": true },
  "application/vnd.hhe.lesson-player": { "source": "iana", "extensions": ["les"] },
  "application/vnd.hl7cda+xml": { "source": "iana", "charset": "UTF-8", "compressible": true },
  "application/vnd.hl7v2+xml": { "source": "iana", "charset": "UTF-8", "compressible": true },
  "application/vnd.hp-hpgl": { "source": "iana", "extensions": ["hpgl"] },
  "application/vnd.hp-hpid": { "source": "iana", "extensions": ["hpid"] },
  "application/vnd.hp-hps": { "source": "iana", "extensions": ["hps"] },
  "application/vnd.hp-jlyt": { "source": "iana", "extensions": ["jlt"] },
  "application/vnd.hp-pcl": { "source": "iana", "extensions": ["pcl"] },
  "application/vnd.hp-pclxl": { "source": "iana", "extensions": ["pclxl"] },
  "application/vnd.httphone": { "source": "iana" },
  "application/vnd.hydrostatix.sof-data": { "source": "iana", "extensions": ["sfd-hdstx"] },
  "application/vnd.hyper+json": { "source": "iana", "compressible": true },
  "application/vnd.hyper-item+json": { "source": "iana", "compressible": true },
  "application/vnd.hyperdrive+json": { "source": "iana", "compressible": true },
  "application/vnd.hzn-3d-crossword": { "source": "iana" },
  "application/vnd.ibm.afplinedata": { "source": "iana" },
  "application/vnd.ibm.electronic-media": { "source": "iana" },
  "application/vnd.ibm.minipay": { "source": "iana", "extensions": ["mpy"] },
  "application/vnd.ibm.modcap": { "source": "iana", "extensions": ["afp", "listafp", "list3820"] },
  "application/vnd.ibm.rights-management": { "source": "iana", "extensions": ["irm"] },
  "application/vnd.ibm.secure-container": { "source": "iana", "extensions": ["sc"] },
  "application/vnd.iccprofile": { "source": "iana", "extensions": ["icc", "icm"] },
  "application/vnd.ieee.1905": { "source": "iana" },
  "application/vnd.igloader": { "source": "iana", "extensions": ["igl"] },
  "application/vnd.imagemeter.folder+zip": { "source": "iana", "compressible": false },
  "application/vnd.imagemeter.image+zip": { "source": "iana", "compressible": false },
  "application/vnd.immervision-ivp": { "source": "iana", "extensions": ["ivp"] },
  "application/vnd.immervision-ivu": { "source": "iana", "extensions": ["ivu"] },
  "application/vnd.ims.imsccv1p1": { "source": "iana" },
  "application/vnd.ims.imsccv1p2": { "source": "iana" },
  "application/vnd.ims.imsccv1p3": { "source": "iana" },
  "application/vnd.ims.lis.v2.result+json": { "source": "iana", "compressible": true },
  "application/vnd.ims.lti.v2.toolconsumerprofile+json": { "source": "iana", "compressible": true },
  "application/vnd.ims.lti.v2.toolproxy+json": { "source": "iana", "compressible": true },
  "application/vnd.ims.lti.v2.toolproxy.id+json": { "source": "iana", "compressible": true },
  "application/vnd.ims.lti.v2.toolsettings+json": { "source": "iana", "compressible": true },
  "application/vnd.ims.lti.v2.toolsettings.simple+json": { "source": "iana", "compressible": true },
  "application/vnd.informedcontrol.rms+xml": { "source": "iana", "compressible": true },
  "application/vnd.informix-visionary": { "source": "iana" },
  "application/vnd.infotech.project": { "source": "iana" },
  "application/vnd.infotech.project+xml": { "source": "iana", "compressible": true },
  "application/vnd.innopath.wamp.notification": { "source": "iana" },
  "application/vnd.insors.igm": { "source": "iana", "extensions": ["igm"] },
  "application/vnd.intercon.formnet": { "source": "iana", "extensions": ["xpw", "xpx"] },
  "application/vnd.intergeo": { "source": "iana", "extensions": ["i2g"] },
  "application/vnd.intertrust.digibox": { "source": "iana" },
  "application/vnd.intertrust.nncp": { "source": "iana" },
  "application/vnd.intu.qbo": { "source": "iana", "extensions": ["qbo"] },
  "application/vnd.intu.qfx": { "source": "iana", "extensions": ["qfx"] },
  "application/vnd.iptc.g2.catalogitem+xml": { "source": "iana", "compressible": true },
  "application/vnd.iptc.g2.conceptitem+xml": { "source": "iana", "compressible": true },
  "application/vnd.iptc.g2.knowledgeitem+xml": { "source": "iana", "compressible": true },
  "application/vnd.iptc.g2.newsitem+xml": { "source": "iana", "compressible": true },
  "application/vnd.iptc.g2.newsmessage+xml": { "source": "iana", "compressible": true },
  "application/vnd.iptc.g2.packageitem+xml": { "source": "iana", "compressible": true },
  "application/vnd.iptc.g2.planningitem+xml": { "source": "iana", "compressible": true },
  "application/vnd.ipunplugged.rcprofile": { "source": "iana", "extensions": ["rcprofile"] },
  "application/vnd.irepository.package+xml": { "source": "iana", "compressible": true, "extensions": ["irp"] },
  "application/vnd.is-xpr": { "source": "iana", "extensions": ["xpr"] },
  "application/vnd.isac.fcs": { "source": "iana", "extensions": ["fcs"] },
  "application/vnd.iso11783-10+zip": { "source": "iana", "compressible": false },
  "application/vnd.jam": { "source": "iana", "extensions": ["jam"] },
  "application/vnd.japannet-directory-service": { "source": "iana" },
  "application/vnd.japannet-jpnstore-wakeup": { "source": "iana" },
  "application/vnd.japannet-payment-wakeup": { "source": "iana" },
  "application/vnd.japannet-registration": { "source": "iana" },
  "application/vnd.japannet-registration-wakeup": { "source": "iana" },
  "application/vnd.japannet-setstore-wakeup": { "source": "iana" },
  "application/vnd.japannet-verification": { "source": "iana" },
  "application/vnd.japannet-verification-wakeup": { "source": "iana" },
  "application/vnd.jcp.javame.midlet-rms": { "source": "iana", "extensions": ["rms"] },
  "application/vnd.jisp": { "source": "iana", "extensions": ["jisp"] },
  "application/vnd.joost.joda-archive": { "source": "iana", "extensions": ["joda"] },
  "application/vnd.jsk.isdn-ngn": { "source": "iana" },
  "application/vnd.kahootz": { "source": "iana", "extensions": ["ktz", "ktr"] },
  "application/vnd.kde.karbon": { "source": "iana", "extensions": ["karbon"] },
  "application/vnd.kde.kchart": { "source": "iana", "extensions": ["chrt"] },
  "application/vnd.kde.kformula": { "source": "iana", "extensions": ["kfo"] },
  "application/vnd.kde.kivio": { "source": "iana", "extensions": ["flw"] },
  "application/vnd.kde.kontour": { "source": "iana", "extensions": ["kon"] },
  "application/vnd.kde.kpresenter": { "source": "iana", "extensions": ["kpr", "kpt"] },
  "application/vnd.kde.kspread": { "source": "iana", "extensions": ["ksp"] },
  "application/vnd.kde.kword": { "source": "iana", "extensions": ["kwd", "kwt"] },
  "application/vnd.kenameaapp": { "source": "iana", "extensions": ["htke"] },
  "application/vnd.kidspiration": { "source": "iana", "extensions": ["kia"] },
  "application/vnd.kinar": { "source": "iana", "extensions": ["kne", "knp"] },
  "application/vnd.koan": { "source": "iana", "extensions": ["skp", "skd", "skt", "skm"] },
  "application/vnd.kodak-descriptor": { "source": "iana", "extensions": ["sse"] },
  "application/vnd.las": { "source": "iana" },
  "application/vnd.las.las+json": { "source": "iana", "compressible": true },
  "application/vnd.las.las+xml": { "source": "iana", "compressible": true, "extensions": ["lasxml"] },
  "application/vnd.laszip": { "source": "iana" },
  "application/vnd.leap+json": { "source": "iana", "compressible": true },
  "application/vnd.liberty-request+xml": { "source": "iana", "compressible": true },
  "application/vnd.llamagraphics.life-balance.desktop": { "source": "iana", "extensions": ["lbd"] },
  "application/vnd.llamagraphics.life-balance.exchange+xml": { "source": "iana", "compressible": true, "extensions": ["lbe"] },
  "application/vnd.logipipe.circuit+zip": { "source": "iana", "compressible": false },
  "application/vnd.loom": { "source": "iana" },
  "application/vnd.lotus-1-2-3": { "source": "iana", "extensions": ["123"] },
  "application/vnd.lotus-approach": { "source": "iana", "extensions": ["apr"] },
  "application/vnd.lotus-freelance": { "source": "iana", "extensions": ["pre"] },
  "application/vnd.lotus-notes": { "source": "iana", "extensions": ["nsf"] },
  "application/vnd.lotus-organizer": { "source": "iana", "extensions": ["org"] },
  "application/vnd.lotus-screencam": { "source": "iana", "extensions": ["scm"] },
  "application/vnd.lotus-wordpro": { "source": "iana", "extensions": ["lwp"] },
  "application/vnd.macports.portpkg": { "source": "iana", "extensions": ["portpkg"] },
  "application/vnd.mapbox-vector-tile": { "source": "iana", "extensions": ["mvt"] },
  "application/vnd.marlin.drm.actiontoken+xml": { "source": "iana", "compressible": true },
  "application/vnd.marlin.drm.conftoken+xml": { "source": "iana", "compressible": true },
  "application/vnd.marlin.drm.license+xml": { "source": "iana", "compressible": true },
  "application/vnd.marlin.drm.mdcf": { "source": "iana" },
  "application/vnd.mason+json": { "source": "iana", "compressible": true },
  "application/vnd.maxar.archive.3tz+zip": { "source": "iana", "compressible": false },
  "application/vnd.maxmind.maxmind-db": { "source": "iana" },
  "application/vnd.mcd": { "source": "iana", "extensions": ["mcd"] },
  "application/vnd.medcalcdata": { "source": "iana", "extensions": ["mc1"] },
  "application/vnd.mediastation.cdkey": { "source": "iana", "extensions": ["cdkey"] },
  "application/vnd.meridian-slingshot": { "source": "iana" },
  "application/vnd.mfer": { "source": "iana", "extensions": ["mwf"] },
  "application/vnd.mfmp": { "source": "iana", "extensions": ["mfm"] },
  "application/vnd.micro+json": { "source": "iana", "compressible": true },
  "application/vnd.micrografx.flo": { "source": "iana", "extensions": ["flo"] },
  "application/vnd.micrografx.igx": { "source": "iana", "extensions": ["igx"] },
  "application/vnd.microsoft.portable-executable": { "source": "iana" },
  "application/vnd.microsoft.windows.thumbnail-cache": { "source": "iana" },
  "application/vnd.miele+json": { "source": "iana", "compressible": true },
  "application/vnd.mif": { "source": "iana", "extensions": ["mif"] },
  "application/vnd.minisoft-hp3000-save": { "source": "iana" },
  "application/vnd.mitsubishi.misty-guard.trustweb": { "source": "iana" },
  "application/vnd.mobius.daf": { "source": "iana", "extensions": ["daf"] },
  "application/vnd.mobius.dis": { "source": "iana", "extensions": ["dis"] },
  "application/vnd.mobius.mbk": { "source": "iana", "extensions": ["mbk"] },
  "application/vnd.mobius.mqy": { "source": "iana", "extensions": ["mqy"] },
  "application/vnd.mobius.msl": { "source": "iana", "extensions": ["msl"] },
  "application/vnd.mobius.plc": { "source": "iana", "extensions": ["plc"] },
  "application/vnd.mobius.txf": { "source": "iana", "extensions": ["txf"] },
  "application/vnd.mophun.application": { "source": "iana", "extensions": ["mpn"] },
  "application/vnd.mophun.certificate": { "source": "iana", "extensions": ["mpc"] },
  "application/vnd.motorola.flexsuite": { "source": "iana" },
  "application/vnd.motorola.flexsuite.adsi": { "source": "iana" },
  "application/vnd.motorola.flexsuite.fis": { "source": "iana" },
  "application/vnd.motorola.flexsuite.gotap": { "source": "iana" },
  "application/vnd.motorola.flexsuite.kmr": { "source": "iana" },
  "application/vnd.motorola.flexsuite.ttc": { "source": "iana" },
  "application/vnd.motorola.flexsuite.wem": { "source": "iana" },
  "application/vnd.motorola.iprm": { "source": "iana" },
  "application/vnd.mozilla.xul+xml": { "source": "iana", "compressible": true, "extensions": ["xul"] },
  "application/vnd.ms-3mfdocument": { "source": "iana" },
  "application/vnd.ms-artgalry": { "source": "iana", "extensions": ["cil"] },
  "application/vnd.ms-asf": { "source": "iana" },
  "application/vnd.ms-cab-compressed": { "source": "iana", "extensions": ["cab"] },
  "application/vnd.ms-color.iccprofile": { "source": "apache" },
  "application/vnd.ms-excel": { "source": "iana", "compressible": false, "extensions": ["xls", "xlm", "xla", "xlc", "xlt", "xlw"] },
  "application/vnd.ms-excel.addin.macroenabled.12": { "source": "iana", "extensions": ["xlam"] },
  "application/vnd.ms-excel.sheet.binary.macroenabled.12": { "source": "iana", "extensions": ["xlsb"] },
  "application/vnd.ms-excel.sheet.macroenabled.12": { "source": "iana", "extensions": ["xlsm"] },
  "application/vnd.ms-excel.template.macroenabled.12": { "source": "iana", "extensions": ["xltm"] },
  "application/vnd.ms-fontobject": { "source": "iana", "compressible": true, "extensions": ["eot"] },
  "application/vnd.ms-htmlhelp": { "source": "iana", "extensions": ["chm"] },
  "application/vnd.ms-ims": { "source": "iana", "extensions": ["ims"] },
  "application/vnd.ms-lrm": { "source": "iana", "extensions": ["lrm"] },
  "application/vnd.ms-office.activex+xml": { "source": "iana", "compressible": true },
  "application/vnd.ms-officetheme": { "source": "iana", "extensions": ["thmx"] },
  "application/vnd.ms-opentype": { "source": "apache", "compressible": true },
  "application/vnd.ms-outlook": { "compressible": false, "extensions": ["msg"] },
  "application/vnd.ms-package.obfuscated-opentype": { "source": "apache" },
  "application/vnd.ms-pki.seccat": { "source": "apache", "extensions": ["cat"] },
  "application/vnd.ms-pki.stl": { "source": "apache", "extensions": ["stl"] },
  "application/vnd.ms-playready.initiator+xml": { "source": "iana", "compressible": true },
  "application/vnd.ms-powerpoint": { "source": "iana", "compressible": false, "extensions": ["ppt", "pps", "pot"] },
  "application/vnd.ms-powerpoint.addin.macroenabled.12": { "source": "iana", "extensions": ["ppam"] },
  "application/vnd.ms-powerpoint.presentation.macroenabled.12": { "source": "iana", "extensions": ["pptm"] },
  "application/vnd.ms-powerpoint.slide.macroenabled.12": { "source": "iana", "extensions": ["sldm"] },
  "application/vnd.ms-powerpoint.slideshow.macroenabled.12": { "source": "iana", "extensions": ["ppsm"] },
  "application/vnd.ms-powerpoint.template.macroenabled.12": { "source": "iana", "extensions": ["potm"] },
  "application/vnd.ms-printdevicecapabilities+xml": { "source": "iana", "compressible": true },
  "application/vnd.ms-printing.printticket+xml": { "source": "apache", "compressible": true },
  "application/vnd.ms-printschematicket+xml": { "source": "iana", "compressible": true },
  "application/vnd.ms-project": { "source": "iana", "extensions": ["mpp", "mpt"] },
  "application/vnd.ms-tnef": { "source": "iana" },
  "application/vnd.ms-windows.devicepairing": { "source": "iana" },
  "application/vnd.ms-windows.nwprinting.oob": { "source": "iana" },
  "application/vnd.ms-windows.printerpairing": { "source": "iana" },
  "application/vnd.ms-windows.wsd.oob": { "source": "iana" },
  "application/vnd.ms-wmdrm.lic-chlg-req": { "source": "iana" },
  "application/vnd.ms-wmdrm.lic-resp": { "source": "iana" },
  "application/vnd.ms-wmdrm.meter-chlg-req": { "source": "iana" },
  "application/vnd.ms-wmdrm.meter-resp": { "source": "iana" },
  "application/vnd.ms-word.document.macroenabled.12": { "source": "iana", "extensions": ["docm"] },
  "application/vnd.ms-word.template.macroenabled.12": { "source": "iana", "extensions": ["dotm"] },
  "application/vnd.ms-works": { "source": "iana", "extensions": ["wps", "wks", "wcm", "wdb"] },
  "application/vnd.ms-wpl": { "source": "iana", "extensions": ["wpl"] },
  "application/vnd.ms-xpsdocument": { "source": "iana", "compressible": false, "extensions": ["xps"] },
  "application/vnd.msa-disk-image": { "source": "iana" },
  "application/vnd.mseq": { "source": "iana", "extensions": ["mseq"] },
  "application/vnd.msign": { "source": "iana" },
  "application/vnd.multiad.creator": { "source": "iana" },
  "application/vnd.multiad.creator.cif": { "source": "iana" },
  "application/vnd.music-niff": { "source": "iana" },
  "application/vnd.musician": { "source": "iana", "extensions": ["mus"] },
  "application/vnd.muvee.style": { "source": "iana", "extensions": ["msty"] },
  "application/vnd.mynfc": { "source": "iana", "extensions": ["taglet"] },
  "application/vnd.nacamar.ybrid+json": { "source": "iana", "compressible": true },
  "application/vnd.ncd.control": { "source": "iana" },
  "application/vnd.ncd.reference": { "source": "iana" },
  "application/vnd.nearst.inv+json": { "source": "iana", "compressible": true },
  "application/vnd.nebumind.line": { "source": "iana" },
  "application/vnd.nervana": { "source": "iana" },
  "application/vnd.netfpx": { "source": "iana" },
  "application/vnd.neurolanguage.nlu": { "source": "iana", "extensions": ["nlu"] },
  "application/vnd.nimn": { "source": "iana" },
  "application/vnd.nintendo.nitro.rom": { "source": "iana" },
  "application/vnd.nintendo.snes.rom": { "source": "iana" },
  "application/vnd.nitf": { "source": "iana", "extensions": ["ntf", "nitf"] },
  "application/vnd.noblenet-directory": { "source": "iana", "extensions": ["nnd"] },
  "application/vnd.noblenet-sealer": { "source": "iana", "extensions": ["nns"] },
  "application/vnd.noblenet-web": { "source": "iana", "extensions": ["nnw"] },
  "application/vnd.nokia.catalogs": { "source": "iana" },
  "application/vnd.nokia.conml+wbxml": { "source": "iana" },
  "application/vnd.nokia.conml+xml": { "source": "iana", "compressible": true },
  "application/vnd.nokia.iptv.config+xml": { "source": "iana", "compressible": true },
  "application/vnd.nokia.isds-radio-presets": { "source": "iana" },
  "application/vnd.nokia.landmark+wbxml": { "source": "iana" },
  "application/vnd.nokia.landmark+xml": { "source": "iana", "compressible": true },
  "application/vnd.nokia.landmarkcollection+xml": { "source": "iana", "compressible": true },
  "application/vnd.nokia.n-gage.ac+xml": { "source": "iana", "compressible": true, "extensions": ["ac"] },
  "application/vnd.nokia.n-gage.data": { "source": "iana", "extensions": ["ngdat"] },
  "application/vnd.nokia.n-gage.symbian.install": { "source": "iana", "extensions": ["n-gage"] },
  "application/vnd.nokia.ncd": { "source": "iana" },
  "application/vnd.nokia.pcd+wbxml": { "source": "iana" },
  "application/vnd.nokia.pcd+xml": { "source": "iana", "compressible": true },
  "application/vnd.nokia.radio-preset": { "source": "iana", "extensions": ["rpst"] },
  "application/vnd.nokia.radio-presets": { "source": "iana", "extensions": ["rpss"] },
  "application/vnd.novadigm.edm": { "source": "iana", "extensions": ["edm"] },
  "application/vnd.novadigm.edx": { "source": "iana", "extensions": ["edx"] },
  "application/vnd.novadigm.ext": { "source": "iana", "extensions": ["ext"] },
  "application/vnd.ntt-local.content-share": { "source": "iana" },
  "application/vnd.ntt-local.file-transfer": { "source": "iana" },
  "application/vnd.ntt-local.ogw_remote-access": { "source": "iana" },
  "application/vnd.ntt-local.sip-ta_remote": { "source": "iana" },
  "application/vnd.ntt-local.sip-ta_tcp_stream": { "source": "iana" },
  "application/vnd.oasis.opendocument.chart": { "source": "iana", "extensions": ["odc"] },
  "application/vnd.oasis.opendocument.chart-template": { "source": "iana", "extensions": ["otc"] },
  "application/vnd.oasis.opendocument.database": { "source": "iana", "extensions": ["odb"] },
  "application/vnd.oasis.opendocument.formula": { "source": "iana", "extensions": ["odf"] },
  "application/vnd.oasis.opendocument.formula-template": { "source": "iana", "extensions": ["odft"] },
  "application/vnd.oasis.opendocument.graphics": { "source": "iana", "compressible": false, "extensions": ["odg"] },
  "application/vnd.oasis.opendocument.graphics-template": { "source": "iana", "extensions": ["otg"] },
  "application/vnd.oasis.opendocument.image": { "source": "iana", "extensions": ["odi"] },
  "application/vnd.oasis.opendocument.image-template": { "source": "iana", "extensions": ["oti"] },
  "application/vnd.oasis.opendocument.presentation": { "source": "iana", "compressible": false, "extensions": ["odp"] },
  "application/vnd.oasis.opendocument.presentation-template": { "source": "iana", "extensions": ["otp"] },
  "application/vnd.oasis.opendocument.spreadsheet": { "source": "iana", "compressible": false, "extensions": ["ods"] },
  "application/vnd.oasis.opendocument.spreadsheet-template": { "source": "iana", "extensions": ["ots"] },
  "application/vnd.oasis.opendocument.text": { "source": "iana", "compressible": false, "extensions": ["odt"] },
  "application/vnd.oasis.opendocument.text-master": { "source": "iana", "extensions": ["odm"] },
  "application/vnd.oasis.opendocument.text-template": { "source": "iana", "extensions": ["ott"] },
  "application/vnd.oasis.opendocument.text-web": { "source": "iana", "extensions": ["oth"] },
  "application/vnd.obn": { "source": "iana" },
  "application/vnd.ocf+cbor": { "source": "iana" },
  "application/vnd.oci.image.manifest.v1+json": { "source": "iana", "compressible": true },
  "application/vnd.oftn.l10n+json": { "source": "iana", "compressible": true },
  "application/vnd.oipf.contentaccessdownload+xml": { "source": "iana", "compressible": true },
  "application/vnd.oipf.contentaccessstreaming+xml": { "source": "iana", "compressible": true },
  "application/vnd.oipf.cspg-hexbinary": { "source": "iana" },
  "application/vnd.oipf.dae.svg+xml": { "source": "iana", "compressible": true },
  "application/vnd.oipf.dae.xhtml+xml": { "source": "iana", "compressible": true },
  "application/vnd.oipf.mippvcontrolmessage+xml": { "source": "iana", "compressible": true },
  "application/vnd.oipf.pae.gem": { "source": "iana" },
  "application/vnd.oipf.spdiscovery+xml": { "source": "iana", "compressible": true },
  "application/vnd.oipf.spdlist+xml": { "source": "iana", "compressible": true },
  "application/vnd.oipf.ueprofile+xml": { "source": "iana", "compressible": true },
  "application/vnd.oipf.userprofile+xml": { "source": "iana", "compressible": true },
  "application/vnd.olpc-sugar": { "source": "iana", "extensions": ["xo"] },
  "application/vnd.oma-scws-config": { "source": "iana" },
  "application/vnd.oma-scws-http-request": { "source": "iana" },
  "application/vnd.oma-scws-http-response": { "source": "iana" },
  "application/vnd.oma.bcast.associated-procedure-parameter+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.bcast.drm-trigger+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.bcast.imd+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.bcast.ltkm": { "source": "iana" },
  "application/vnd.oma.bcast.notification+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.bcast.provisioningtrigger": { "source": "iana" },
  "application/vnd.oma.bcast.sgboot": { "source": "iana" },
  "application/vnd.oma.bcast.sgdd+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.bcast.sgdu": { "source": "iana" },
  "application/vnd.oma.bcast.simple-symbol-container": { "source": "iana" },
  "application/vnd.oma.bcast.smartcard-trigger+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.bcast.sprov+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.bcast.stkm": { "source": "iana" },
  "application/vnd.oma.cab-address-book+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.cab-feature-handler+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.cab-pcc+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.cab-subs-invite+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.cab-user-prefs+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.dcd": { "source": "iana" },
  "application/vnd.oma.dcdc": { "source": "iana" },
  "application/vnd.oma.dd2+xml": { "source": "iana", "compressible": true, "extensions": ["dd2"] },
  "application/vnd.oma.drm.risd+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.group-usage-list+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.lwm2m+cbor": { "source": "iana" },
  "application/vnd.oma.lwm2m+json": { "source": "iana", "compressible": true },
  "application/vnd.oma.lwm2m+tlv": { "source": "iana" },
  "application/vnd.oma.pal+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.poc.detailed-progress-report+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.poc.final-report+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.poc.groups+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.poc.invocation-descriptor+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.poc.optimized-progress-report+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.push": { "source": "iana" },
  "application/vnd.oma.scidm.messages+xml": { "source": "iana", "compressible": true },
  "application/vnd.oma.xcap-directory+xml": { "source": "iana", "compressible": true },
  "application/vnd.omads-email+xml": { "source": "iana", "charset": "UTF-8", "compressible": true },
  "application/vnd.omads-file+xml": { "source": "iana", "charset": "UTF-8", "compressible": true },
  "application/vnd.omads-folder+xml": { "source": "iana", "charset": "UTF-8", "compressible": true },
  "application/vnd.omaloc-supl-init": { "source": "iana" },
  "application/vnd.onepager": { "source": "iana" },
  "application/vnd.onepagertamp": { "source": "iana" },
  "application/vnd.onepagertamx": { "source": "iana" },
  "application/vnd.onepagertat": { "source": "iana" },
  "application/vnd.onepagertatp": { "source": "iana" },
  "application/vnd.onepagertatx": { "source": "iana" },
  "application/vnd.openblox.game+xml": { "source": "iana", "compressible": true, "extensions": ["obgx"] },
  "application/vnd.openblox.game-binary": { "source": "iana" },
  "application/vnd.openeye.oeb": { "source": "iana" },
  "application/vnd.openofficeorg.extension": { "source": "apache", "extensions": ["oxt"] },
  "application/vnd.openstreetmap.data+xml": { "source": "iana", "compressible": true, "extensions": ["osm"] },
  "application/vnd.opentimestamps.ots": { "source": "iana" },
  "application/vnd.openxmlformats-officedocument.custom-properties+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.customxmlproperties+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.drawing+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.drawingml.chart+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.drawingml.chartshapes+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.drawingml.diagramcolors+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.drawingml.diagramdata+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.drawingml.diagramlayout+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.drawingml.diagramstyle+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.extended-properties+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.presentationml.commentauthors+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.presentationml.comments+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.presentationml.handoutmaster+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.presentationml.notesmaster+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.presentationml.notesslide+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.presentationml.presentation": { "source": "iana", "compressible": false, "extensions": ["pptx"] },
  "application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.presentationml.presprops+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.presentationml.slide": { "source": "iana", "extensions": ["sldx"] },
  "application/vnd.openxmlformats-officedocument.presentationml.slide+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.presentationml.slidelayout+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.presentationml.slidemaster+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.presentationml.slideshow": { "source": "iana", "extensions": ["ppsx"] },
  "application/vnd.openxmlformats-officedocument.presentationml.slideshow.main+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.presentationml.slideupdateinfo+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.presentationml.tablestyles+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.presentationml.tags+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.presentationml.template": { "source": "iana", "extensions": ["potx"] },
  "application/vnd.openxmlformats-officedocument.presentationml.template.main+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.presentationml.viewprops+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.calcchain+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.chartsheet+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.comments+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.connections+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.dialogsheet+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.externallink+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.pivotcachedefinition+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.pivotcacherecords+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.pivottable+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.querytable+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.revisionheaders+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.revisionlog+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.sharedstrings+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet": { "source": "iana", "compressible": false, "extensions": ["xlsx"] },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheetmetadata+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.table+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.tablesinglecells+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.template": { "source": "iana", "extensions": ["xltx"] },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.template.main+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.usernames+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.volatiledependencies+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.theme+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.themeoverride+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.vmldrawing": { "source": "iana" },
  "application/vnd.openxmlformats-officedocument.wordprocessingml.comments+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document": { "source": "iana", "compressible": false, "extensions": ["docx"] },
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document.glossary+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.wordprocessingml.endnotes+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.wordprocessingml.fonttable+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.wordprocessingml.footnotes+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.wordprocessingml.numbering+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.wordprocessingml.template": { "source": "iana", "extensions": ["dotx"] },
  "application/vnd.openxmlformats-officedocument.wordprocessingml.template.main+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-officedocument.wordprocessingml.websettings+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-package.core-properties+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-package.digital-signature-xmlsignature+xml": { "source": "iana", "compressible": true },
  "application/vnd.openxmlformats-package.relationships+xml": { "source": "iana", "compressible": true },
  "application/vnd.oracle.resource+json": { "source": "iana", "compressible": true },
  "application/vnd.orange.indata": { "source": "iana" },
  "application/vnd.osa.netdeploy": { "source": "iana" },
  "application/vnd.osgeo.mapguide.package": { "source": "iana", "extensions": ["mgp"] },
  "application/vnd.osgi.bundle": { "source": "iana" },
  "application/vnd.osgi.dp": { "source": "iana", "extensions": ["dp"] },
  "application/vnd.osgi.subsystem": { "source": "iana", "extensions": ["esa"] },
  "application/vnd.otps.ct-kip+xml": { "source": "iana", "compressible": true },
  "application/vnd.oxli.countgraph": { "source": "iana" },
  "application/vnd.pagerduty+json": { "source": "iana", "compressible": true },
  "application/vnd.palm": { "source": "iana", "extensions": ["pdb", "pqa", "oprc"] },
  "application/vnd.panoply": { "source": "iana" },
  "application/vnd.paos.xml": { "source": "iana" },
  "application/vnd.patentdive": { "source": "iana" },
  "application/vnd.patientecommsdoc": { "source": "iana" },
  "application/vnd.pawaafile": { "source": "iana", "extensions": ["paw"] },
  "application/vnd.pcos": { "source": "iana" },
  "application/vnd.pg.format": { "source": "iana", "extensions": ["str"] },
  "application/vnd.pg.osasli": { "source": "iana", "extensions": ["ei6"] },
  "application/vnd.piaccess.application-licence": { "source": "iana" },
  "application/vnd.picsel": { "source": "iana", "extensions": ["efif"] },
  "application/vnd.pmi.widget": { "source": "iana", "extensions": ["wg"] },
  "application/vnd.poc.group-advertisement+xml": { "source": "iana", "compressible": true },
  "application/vnd.pocketlearn": { "source": "iana", "extensions": ["plf"] },
  "application/vnd.powerbuilder6": { "source": "iana", "extensions": ["pbd"] },
  "application/vnd.powerbuilder6-s": { "source": "iana" },
  "application/vnd.powerbuilder7": { "source": "iana" },
  "application/vnd.powerbuilder7-s": { "source": "iana" },
  "application/vnd.powerbuilder75": { "source": "iana" },
  "application/vnd.powerbuilder75-s": { "source": "iana" },
  "application/vnd.preminet": { "source": "iana" },
  "application/vnd.previewsystems.box": { "source": "iana", "extensions": ["box"] },
  "application/vnd.proteus.magazine": { "source": "iana", "extensions": ["mgz"] },
  "application/vnd.psfs": { "source": "iana" },
  "application/vnd.publishare-delta-tree": { "source": "iana", "extensions": ["qps"] },
  "application/vnd.pvi.ptid1": { "source": "iana", "extensions": ["ptid"] },
  "application/vnd.pwg-multiplexed": { "source": "iana" },
  "application/vnd.pwg-xhtml-print+xml": { "source": "iana", "compressible": true },
  "application/vnd.qualcomm.brew-app-res": { "source": "iana" },
  "application/vnd.quarantainenet": { "source": "iana" },
  "application/vnd.quark.quarkxpress": { "source": "iana", "extensions": ["qxd", "qxt", "qwd", "qwt", "qxl", "qxb"] },
  "application/vnd.quobject-quoxdocument": { "source": "iana" },
  "application/vnd.radisys.moml+xml": { "source": "iana", "compressible": true },
  "application/vnd.radisys.msml+xml": { "source": "iana", "compressible": true },
  "application/vnd.radisys.msml-audit+xml": { "source": "iana", "compressible": true },
  "application/vnd.radisys.msml-audit-conf+xml": { "source": "iana", "compressible": true },
  "application/vnd.radisys.msml-audit-conn+xml": { "source": "iana", "compressible": true },
  "application/vnd.radisys.msml-audit-dialog+xml": { "source": "iana", "compressible": true },
  "application/vnd.radisys.msml-audit-stream+xml": { "source": "iana", "compressible": true },
  "application/vnd.radisys.msml-conf+xml": { "source": "iana", "compressible": true },
  "application/vnd.radisys.msml-dialog+xml": { "source": "iana", "compressible": true },
  "application/vnd.radisys.msml-dialog-base+xml": { "source": "iana", "compressible": true },
  "application/vnd.radisys.msml-dialog-fax-detect+xml": { "source": "iana", "compressible": true },
  "application/vnd.radisys.msml-dialog-fax-sendrecv+xml": { "source": "iana", "compressible": true },
  "application/vnd.radisys.msml-dialog-group+xml": { "source": "iana", "compressible": true },
  "application/vnd.radisys.msml-dialog-speech+xml": { "source": "iana", "compressible": true },
  "application/vnd.radisys.msml-dialog-transform+xml": { "source": "iana", "compressible": true },
  "application/vnd.rainstor.data": { "source": "iana" },
  "application/vnd.rapid": { "source": "iana" },
  "application/vnd.rar": { "source": "iana", "extensions": ["rar"] },
  "application/vnd.realvnc.bed": { "source": "iana", "extensions": ["bed"] },
  "application/vnd.recordare.musicxml": { "source": "iana", "extensions": ["mxl"] },
  "application/vnd.recordare.musicxml+xml": { "source": "iana", "compressible": true, "extensions": ["musicxml"] },
  "application/vnd.renlearn.rlprint": { "source": "iana" },
  "application/vnd.resilient.logic": { "source": "iana" },
  "application/vnd.restful+json": { "source": "iana", "compressible": true },
  "application/vnd.rig.cryptonote": { "source": "iana", "extensions": ["cryptonote"] },
  "application/vnd.rim.cod": { "source": "apache", "extensions": ["cod"] },
  "application/vnd.rn-realmedia": { "source": "apache", "extensions": ["rm"] },
  "application/vnd.rn-realmedia-vbr": { "source": "apache", "extensions": ["rmvb"] },
  "application/vnd.route66.link66+xml": { "source": "iana", "compressible": true, "extensions": ["link66"] },
  "application/vnd.rs-274x": { "source": "iana" },
  "application/vnd.ruckus.download": { "source": "iana" },
  "application/vnd.s3sms": { "source": "iana" },
  "application/vnd.sailingtracker.track": { "source": "iana", "extensions": ["st"] },
  "application/vnd.sar": { "source": "iana" },
  "application/vnd.sbm.cid": { "source": "iana" },
  "application/vnd.sbm.mid2": { "source": "iana" },
  "application/vnd.scribus": { "source": "iana" },
  "application/vnd.sealed.3df": { "source": "iana" },
  "application/vnd.sealed.csf": { "source": "iana" },
  "application/vnd.sealed.doc": { "source": "iana" },
  "application/vnd.sealed.eml": { "source": "iana" },
  "application/vnd.sealed.mht": { "source": "iana" },
  "application/vnd.sealed.net": { "source": "iana" },
  "application/vnd.sealed.ppt": { "source": "iana" },
  "application/vnd.sealed.tiff": { "source": "iana" },
  "application/vnd.sealed.xls": { "source": "iana" },
  "application/vnd.sealedmedia.softseal.html": { "source": "iana" },
  "application/vnd.sealedmedia.softseal.pdf": { "source": "iana" },
  "application/vnd.seemail": { "source": "iana", "extensions": ["see"] },
  "application/vnd.seis+json": { "source": "iana", "compressible": true },
  "application/vnd.sema": { "source": "iana", "extensions": ["sema"] },
  "application/vnd.semd": { "source": "iana", "extensions": ["semd"] },
  "application/vnd.semf": { "source": "iana", "extensions": ["semf"] },
  "application/vnd.shade-save-file": { "source": "iana" },
  "application/vnd.shana.informed.formdata": { "source": "iana", "extensions": ["ifm"] },
  "application/vnd.shana.informed.formtemplate": { "source": "iana", "extensions": ["itp"] },
  "application/vnd.shana.informed.interchange": { "source": "iana", "extensions": ["iif"] },
  "application/vnd.shana.informed.package": { "source": "iana", "extensions": ["ipk"] },
  "application/vnd.shootproof+json": { "source": "iana", "compressible": true },
  "application/vnd.shopkick+json": { "source": "iana", "compressible": true },
  "application/vnd.shp": { "source": "iana" },
  "application/vnd.shx": { "source": "iana" },
  "application/vnd.sigrok.session": { "source": "iana" },
  "application/vnd.simtech-mindmapper": { "source": "iana", "extensions": ["twd", "twds"] },
  "application/vnd.siren+json": { "source": "iana", "compressible": true },
  "application/vnd.smaf": { "source": "iana", "extensions": ["mmf"] },
  "application/vnd.smart.notebook": { "source": "iana" },
  "application/vnd.smart.teacher": { "source": "iana", "extensions": ["teacher"] },
  "application/vnd.snesdev-page-table": { "source": "iana" },
  "application/vnd.software602.filler.form+xml": { "source": "iana", "compressible": true, "extensions": ["fo"] },
  "application/vnd.software602.filler.form-xml-zip": { "source": "iana" },
  "application/vnd.solent.sdkm+xml": { "source": "iana", "compressible": true, "extensions": ["sdkm", "sdkd"] },
  "application/vnd.spotfire.dxp": { "source": "iana", "extensions": ["dxp"] },
  "application/vnd.spotfire.sfs": { "source": "iana", "extensions": ["sfs"] },
  "application/vnd.sqlite3": { "source": "iana" },
  "application/vnd.sss-cod": { "source": "iana" },
  "application/vnd.sss-dtf": { "source": "iana" },
  "application/vnd.sss-ntf": { "source": "iana" },
  "application/vnd.stardivision.calc": { "source": "apache", "extensions": ["sdc"] },
  "application/vnd.stardivision.draw": { "source": "apache", "extensions": ["sda"] },
  "application/vnd.stardivision.impress": { "source": "apache", "extensions": ["sdd"] },
  "application/vnd.stardivision.math": { "source": "apache", "extensions": ["smf"] },
  "application/vnd.stardivision.writer": { "source": "apache", "extensions": ["sdw", "vor"] },
  "application/vnd.stardivision.writer-global": { "source": "apache", "extensions": ["sgl"] },
  "application/vnd.stepmania.package": { "source": "iana", "extensions": ["smzip"] },
  "application/vnd.stepmania.stepchart": { "source": "iana", "extensions": ["sm"] },
  "application/vnd.street-stream": { "source": "iana" },
  "application/vnd.sun.wadl+xml": { "source": "iana", "compressible": true, "extensions": ["wadl"] },
  "application/vnd.sun.xml.calc": { "source": "apache", "extensions": ["sxc"] },
  "application/vnd.sun.xml.calc.template": { "source": "apache", "extensions": ["stc"] },
  "application/vnd.sun.xml.draw": { "source": "apache", "extensions": ["sxd"] },
  "application/vnd.sun.xml.draw.template": { "source": "apache", "extensions": ["std"] },
  "application/vnd.sun.xml.impress": { "source": "apache", "extensions": ["sxi"] },
  "application/vnd.sun.xml.impress.template": { "source": "apache", "extensions": ["sti"] },
  "application/vnd.sun.xml.math": { "source": "apache", "extensions": ["sxm"] },
  "application/vnd.sun.xml.writer": { "source": "apache", "extensions": ["sxw"] },
  "application/vnd.sun.xml.writer.global": { "source": "apache", "extensions": ["sxg"] },
  "application/vnd.sun.xml.writer.template": { "source": "apache", "extensions": ["stw"] },
  "application/vnd.sus-calendar": { "source": "iana", "extensions": ["sus", "susp"] },
  "application/vnd.svd": { "source": "iana", "extensions": ["svd"] },
  "application/vnd.swiftview-ics": { "source": "iana" },
  "application/vnd.sycle+xml": { "source": "iana", "compressible": true },
  "application/vnd.syft+json": { "source": "iana", "compressible": true },
  "application/vnd.symbian.install": { "source": "apache", "extensions": ["sis", "sisx"] },
  "application/vnd.syncml+xml": { "source": "iana", "charset": "UTF-8", "compressible": true, "extensions": ["xsm"] },
  "application/vnd.syncml.dm+wbxml": { "source": "iana", "charset": "UTF-8", "extensions": ["bdm"] },
  "application/vnd.syncml.dm+xml": { "source": "iana", "charset": "UTF-8", "compressible": true, "extensions": ["xdm"] },
  "application/vnd.syncml.dm.notification": { "source": "iana" },
  "application/vnd.syncml.dmddf+wbxml": { "source": "iana" },
  "application/vnd.syncml.dmddf+xml": { "source": "iana", "charset": "UTF-8", "compressible": true, "extensions": ["ddf"] },
  "application/vnd.syncml.dmtnds+wbxml": { "source": "iana" },
  "application/vnd.syncml.dmtnds+xml": { "source": "iana", "charset": "UTF-8", "compressible": true },
  "application/vnd.syncml.ds.notification": { "source": "iana" },
  "application/vnd.tableschema+json": { "source": "iana", "compressible": true },
  "application/vnd.tao.intent-module-archive": { "source": "iana", "extensions": ["tao"] },
  "application/vnd.tcpdump.pcap": { "source": "iana", "extensions": ["pcap", "cap", "dmp"] },
  "application/vnd.think-cell.ppttc+json": { "source": "iana", "compressible": true },
  "application/vnd.tmd.mediaflex.api+xml": { "source": "iana", "compressible": true },
  "application/vnd.tml": { "source": "iana" },
  "application/vnd.tmobile-livetv": { "source": "iana", "extensions": ["tmo"] },
  "application/vnd.tri.onesource": { "source": "iana" },
  "application/vnd.trid.tpt": { "source": "iana", "extensions": ["tpt"] },
  "application/vnd.triscape.mxs": { "source": "iana", "extensions": ["mxs"] },
  "application/vnd.trueapp": { "source": "iana", "extensions": ["tra"] },
  "application/vnd.truedoc": { "source": "iana" },
  "application/vnd.ubisoft.webplayer": { "source": "iana" },
  "application/vnd.ufdl": { "source": "iana", "extensions": ["ufd", "ufdl"] },
  "application/vnd.uiq.theme": { "source": "iana", "extensions": ["utz"] },
  "application/vnd.umajin": { "source": "iana", "extensions": ["umj"] },
  "application/vnd.unity": { "source": "iana", "extensions": ["unityweb"] },
  "application/vnd.uoml+xml": { "source": "iana", "compressible": true, "extensions": ["uoml"] },
  "application/vnd.uplanet.alert": { "source": "iana" },
  "application/vnd.uplanet.alert-wbxml": { "source": "iana" },
  "application/vnd.uplanet.bearer-choice": { "source": "iana" },
  "application/vnd.uplanet.bearer-choice-wbxml": { "source": "iana" },
  "application/vnd.uplanet.cacheop": { "source": "iana" },
  "application/vnd.uplanet.cacheop-wbxml": { "source": "iana" },
  "application/vnd.uplanet.channel": { "source": "iana" },
  "application/vnd.uplanet.channel-wbxml": { "source": "iana" },
  "application/vnd.uplanet.list": { "source": "iana" },
  "application/vnd.uplanet.list-wbxml": { "source": "iana" },
  "application/vnd.uplanet.listcmd": { "source": "iana" },
  "application/vnd.uplanet.listcmd-wbxml": { "source": "iana" },
  "application/vnd.uplanet.signal": { "source": "iana" },
  "application/vnd.uri-map": { "source": "iana" },
  "application/vnd.valve.source.material": { "source": "iana" },
  "application/vnd.vcx": { "source": "iana", "extensions": ["vcx"] },
  "application/vnd.vd-study": { "source": "iana" },
  "application/vnd.vectorworks": { "source": "iana" },
  "application/vnd.vel+json": { "source": "iana", "compressible": true },
  "application/vnd.verimatrix.vcas": { "source": "iana" },
  "application/vnd.veritone.aion+json": { "source": "iana", "compressible": true },
  "application/vnd.veryant.thin": { "source": "iana" },
  "application/vnd.ves.encrypted": { "source": "iana" },
  "application/vnd.vidsoft.vidconference": { "source": "iana" },
  "application/vnd.visio": { "source": "iana", "extensions": ["vsd", "vst", "vss", "vsw"] },
  "application/vnd.visionary": { "source": "iana", "extensions": ["vis"] },
  "application/vnd.vividence.scriptfile": { "source": "iana" },
  "application/vnd.vsf": { "source": "iana", "extensions": ["vsf"] },
  "application/vnd.wap.sic": { "source": "iana" },
  "application/vnd.wap.slc": { "source": "iana" },
  "application/vnd.wap.wbxml": { "source": "iana", "charset": "UTF-8", "extensions": ["wbxml"] },
  "application/vnd.wap.wmlc": { "source": "iana", "extensions": ["wmlc"] },
  "application/vnd.wap.wmlscriptc": { "source": "iana", "extensions": ["wmlsc"] },
  "application/vnd.webturbo": { "source": "iana", "extensions": ["wtb"] },
  "application/vnd.wfa.dpp": { "source": "iana" },
  "application/vnd.wfa.p2p": { "source": "iana" },
  "application/vnd.wfa.wsc": { "source": "iana" },
  "application/vnd.windows.devicepairing": { "source": "iana" },
  "application/vnd.wmc": { "source": "iana" },
  "application/vnd.wmf.bootstrap": { "source": "iana" },
  "application/vnd.wolfram.mathematica": { "source": "iana" },
  "application/vnd.wolfram.mathematica.package": { "source": "iana" },
  "application/vnd.wolfram.player": { "source": "iana", "extensions": ["nbp"] },
  "application/vnd.wordperfect": { "source": "iana", "extensions": ["wpd"] },
  "application/vnd.wqd": { "source": "iana", "extensions": ["wqd"] },
  "application/vnd.wrq-hp3000-labelled": { "source": "iana" },
  "application/vnd.wt.stf": { "source": "iana", "extensions": ["stf"] },
  "application/vnd.wv.csp+wbxml": { "source": "iana" },
  "application/vnd.wv.csp+xml": { "source": "iana", "compressible": true },
  "application/vnd.wv.ssp+xml": { "source": "iana", "compressible": true },
  "application/vnd.xacml+json": { "source": "iana", "compressible": true },
  "application/vnd.xara": { "source": "iana", "extensions": ["xar"] },
  "application/vnd.xfdl": { "source": "iana", "extensions": ["xfdl"] },
  "application/vnd.xfdl.webform": { "source": "iana" },
  "application/vnd.xmi+xml": { "source": "iana", "compressible": true },
  "application/vnd.xmpie.cpkg": { "source": "iana" },
  "application/vnd.xmpie.dpkg": { "source": "iana" },
  "application/vnd.xmpie.plan": { "source": "iana" },
  "application/vnd.xmpie.ppkg": { "source": "iana" },
  "application/vnd.xmpie.xlim": { "source": "iana" },
  "application/vnd.yamaha.hv-dic": { "source": "iana", "extensions": ["hvd"] },
  "application/vnd.yamaha.hv-script": { "source": "iana", "extensions": ["hvs"] },
  "application/vnd.yamaha.hv-voice": { "source": "iana", "extensions": ["hvp"] },
  "application/vnd.yamaha.openscoreformat": { "source": "iana", "extensions": ["osf"] },
  "application/vnd.yamaha.openscoreformat.osfpvg+xml": { "source": "iana", "compressible": true, "extensions": ["osfpvg"] },
  "application/vnd.yamaha.remote-setup": { "source": "iana" },
  "application/vnd.yamaha.smaf-audio": { "source": "iana", "extensions": ["saf"] },
  "application/vnd.yamaha.smaf-phrase": { "source": "iana", "extensions": ["spf"] },
  "application/vnd.yamaha.through-ngn": { "source": "iana" },
  "application/vnd.yamaha.tunnel-udpencap": { "source": "iana" },
  "application/vnd.yaoweme": { "source": "iana" },
  "application/vnd.yellowriver-custom-menu": { "source": "iana", "extensions": ["cmp"] },
  "application/vnd.youtube.yt": { "source": "iana" },
  "application/vnd.zul": { "source": "iana", "extensions": ["zir", "zirz"] },
  "application/vnd.zzazz.deck+xml": { "source": "iana", "compressible": true, "extensions": ["zaz"] },
  "application/voicexml+xml": { "source": "iana", "compressible": true, "extensions": ["vxml"] },
  "application/voucher-cms+json": { "source": "iana", "compressible": true },
  "application/vq-rtcpxr": { "source": "iana" },
  "application/wasm": { "source": "iana", "compressible": true, "extensions": ["wasm"] },
  "application/watcherinfo+xml": { "source": "iana", "compressible": true, "extensions": ["wif"] },
  "application/webpush-options+json": { "source": "iana", "compressible": true },
  "application/whoispp-query": { "source": "iana" },
  "application/whoispp-response": { "source": "iana" },
  "application/widget": { "source": "iana", "extensions": ["wgt"] },
  "application/winhlp": { "source": "apache", "extensions": ["hlp"] },
  "application/wita": { "source": "iana" },
  "application/wordperfect5.1": { "source": "iana" },
  "application/wsdl+xml": { "source": "iana", "compressible": true, "extensions": ["wsdl"] },
  "application/wspolicy+xml": { "source": "iana", "compressible": true, "extensions": ["wspolicy"] },
  "application/x-7z-compressed": { "source": "apache", "compressible": false, "extensions": ["7z"] },
  "application/x-abiword": { "source": "apache", "extensions": ["abw"] },
  "application/x-ace-compressed": { "source": "apache", "extensions": ["ace"] },
  "application/x-amf": { "source": "apache" },
  "application/x-apple-diskimage": { "source": "apache", "extensions": ["dmg"] },
  "application/x-arj": { "compressible": false, "extensions": ["arj"] },
  "application/x-authorware-bin": { "source": "apache", "extensions": ["aab", "x32", "u32", "vox"] },
  "application/x-authorware-map": { "source": "apache", "extensions": ["aam"] },
  "application/x-authorware-seg": { "source": "apache", "extensions": ["aas"] },
  "application/x-bcpio": { "source": "apache", "extensions": ["bcpio"] },
  "application/x-bdoc": { "compressible": false, "extensions": ["bdoc"] },
  "application/x-bittorrent": { "source": "apache", "extensions": ["torrent"] },
  "application/x-blorb": { "source": "apache", "extensions": ["blb", "blorb"] },
  "application/x-bzip": { "source": "apache", "compressible": false, "extensions": ["bz"] },
  "application/x-bzip2": { "source": "apache", "compressible": false, "extensions": ["bz2", "boz"] },
  "application/x-cbr": { "source": "apache", "extensions": ["cbr", "cba", "cbt", "cbz", "cb7"] },
  "application/x-cdlink": { "source": "apache", "extensions": ["vcd"] },
  "application/x-cfs-compressed": { "source": "apache", "extensions": ["cfs"] },
  "application/x-chat": { "source": "apache", "extensions": ["chat"] },
  "application/x-chess-pgn": { "source": "apache", "extensions": ["pgn"] },
  "application/x-chrome-extension": { "extensions": ["crx"] },
  "application/x-cocoa": { "source": "nginx", "extensions": ["cco"] },
  "application/x-compress": { "source": "apache" },
  "application/x-conference": { "source": "apache", "extensions": ["nsc"] },
  "application/x-cpio": { "source": "apache", "extensions": ["cpio"] },
  "application/x-csh": { "source": "apache", "extensions": ["csh"] },
  "application/x-deb": { "compressible": false },
  "application/x-debian-package": { "source": "apache", "extensions": ["deb", "udeb"] },
  "application/x-dgc-compressed": { "source": "apache", "extensions": ["dgc"] },
  "application/x-director": { "source": "apache", "extensions": ["dir", "dcr", "dxr", "cst", "cct", "cxt", "w3d", "fgd", "swa"] },
  "application/x-doom": { "source": "apache", "extensions": ["wad"] },
  "application/x-dtbncx+xml": { "source": "apache", "compressible": true, "extensions": ["ncx"] },
  "application/x-dtbook+xml": { "source": "apache", "compressible": true, "extensions": ["dtb"] },
  "application/x-dtbresource+xml": { "source": "apache", "compressible": true, "extensions": ["res"] },
  "application/x-dvi": { "source": "apache", "compressible": false, "extensions": ["dvi"] },
  "application/x-envoy": { "source": "apache", "extensions": ["evy"] },
  "application/x-eva": { "source": "apache", "extensions": ["eva"] },
  "application/x-font-bdf": { "source": "apache", "extensions": ["bdf"] },
  "application/x-font-dos": { "source": "apache" },
  "application/x-font-framemaker": { "source": "apache" },
  "application/x-font-ghostscript": { "source": "apache", "extensions": ["gsf"] },
  "application/x-font-libgrx": { "source": "apache" },
  "application/x-font-linux-psf": { "source": "apache", "extensions": ["psf"] },
  "application/x-font-pcf": { "source": "apache", "extensions": ["pcf"] },
  "application/x-font-snf": { "source": "apache", "extensions": ["snf"] },
  "application/x-font-speedo": { "source": "apache" },
  "application/x-font-sunos-news": { "source": "apache" },
  "application/x-font-type1": { "source": "apache", "extensions": ["pfa", "pfb", "pfm", "afm"] },
  "application/x-font-vfont": { "source": "apache" },
  "application/x-freearc": { "source": "apache", "extensions": ["arc"] },
  "application/x-futuresplash": { "source": "apache", "extensions": ["spl"] },
  "application/x-gca-compressed": { "source": "apache", "extensions": ["gca"] },
  "application/x-glulx": { "source": "apache", "extensions": ["ulx"] },
  "application/x-gnumeric": { "source": "apache", "extensions": ["gnumeric"] },
  "application/x-gramps-xml": { "source": "apache", "extensions": ["gramps"] },
  "application/x-gtar": { "source": "apache", "extensions": ["gtar"] },
  "application/x-gzip": { "source": "apache" },
  "application/x-hdf": { "source": "apache", "extensions": ["hdf"] },
  "application/x-httpd-php": { "compressible": true, "extensions": ["php"] },
  "application/x-install-instructions": { "source": "apache", "extensions": ["install"] },
  "application/x-iso9660-image": { "source": "apache", "extensions": ["iso"] },
  "application/x-iwork-keynote-sffkey": { "extensions": ["key"] },
  "application/x-iwork-numbers-sffnumbers": { "extensions": ["numbers"] },
  "application/x-iwork-pages-sffpages": { "extensions": ["pages"] },
  "application/x-java-archive-diff": { "source": "nginx", "extensions": ["jardiff"] },
  "application/x-java-jnlp-file": { "source": "apache", "compressible": false, "extensions": ["jnlp"] },
  "application/x-javascript": { "compressible": true },
  "application/x-keepass2": { "extensions": ["kdbx"] },
  "application/x-latex": { "source": "apache", "compressible": false, "extensions": ["latex"] },
  "application/x-lua-bytecode": { "extensions": ["luac"] },
  "application/x-lzh-compressed": { "source": "apache", "extensions": ["lzh", "lha"] },
  "application/x-makeself": { "source": "nginx", "extensions": ["run"] },
  "application/x-mie": { "source": "apache", "extensions": ["mie"] },
  "application/x-mobipocket-ebook": { "source": "apache", "extensions": ["prc", "mobi"] },
  "application/x-mpegurl": { "compressible": false },
  "application/x-ms-application": { "source": "apache", "extensions": ["application"] },
  "application/x-ms-shortcut": { "source": "apache", "extensions": ["lnk"] },
  "application/x-ms-wmd": { "source": "apache", "extensions": ["wmd"] },
  "application/x-ms-wmz": { "source": "apache", "extensions": ["wmz"] },
  "application/x-ms-xbap": { "source": "apache", "extensions": ["xbap"] },
  "application/x-msaccess": { "source": "apache", "extensions": ["mdb"] },
  "application/x-msbinder": { "source": "apache", "extensions": ["obd"] },
  "application/x-mscardfile": { "source": "apache", "extensions": ["crd"] },
  "application/x-msclip": { "source": "apache", "extensions": ["clp"] },
  "application/x-msdos-program": { "extensions": ["exe"] },
  "application/x-msdownload": { "source": "apache", "extensions": ["exe", "dll", "com", "bat", "msi"] },
  "application/x-msmediaview": { "source": "apache", "extensions": ["mvb", "m13", "m14"] },
  "application/x-msmetafile": { "source": "apache", "extensions": ["wmf", "wmz", "emf", "emz"] },
  "application/x-msmoney": { "source": "apache", "extensions": ["mny"] },
  "application/x-mspublisher": { "source": "apache", "extensions": ["pub"] },
  "application/x-msschedule": { "source": "apache", "extensions": ["scd"] },
  "application/x-msterminal": { "source": "apache", "extensions": ["trm"] },
  "application/x-mswrite": { "source": "apache", "extensions": ["wri"] },
  "application/x-netcdf": { "source": "apache", "extensions": ["nc", "cdf"] },
  "application/x-ns-proxy-autoconfig": { "compressible": true, "extensions": ["pac"] },
  "application/x-nzb": { "source": "apache", "extensions": ["nzb"] },
  "application/x-perl": { "source": "nginx", "extensions": ["pl", "pm"] },
  "application/x-pilot": { "source": "nginx", "extensions": ["prc", "pdb"] },
  "application/x-pkcs12": { "source": "apache", "compressible": false, "extensions": ["p12", "pfx"] },
  "application/x-pkcs7-certificates": { "source": "apache", "extensions": ["p7b", "spc"] },
  "application/x-pkcs7-certreqresp": { "source": "apache", "extensions": ["p7r"] },
  "application/x-pki-message": { "source": "iana" },
  "application/x-rar-compressed": { "source": "apache", "compressible": false, "extensions": ["rar"] },
  "application/x-redhat-package-manager": { "source": "nginx", "extensions": ["rpm"] },
  "application/x-research-info-systems": { "source": "apache", "extensions": ["ris"] },
  "application/x-sea": { "source": "nginx", "extensions": ["sea"] },
  "application/x-sh": { "source": "apache", "compressible": true, "extensions": ["sh"] },
  "application/x-shar": { "source": "apache", "extensions": ["shar"] },
  "application/x-shockwave-flash": { "source": "apache", "compressible": false, "extensions": ["swf"] },
  "application/x-silverlight-app": { "source": "apache", "extensions": ["xap"] },
  "application/x-sql": { "source": "apache", "extensions": ["sql"] },
  "application/x-stuffit": { "source": "apache", "compressible": false, "extensions": ["sit"] },
  "application/x-stuffitx": { "source": "apache", "extensions": ["sitx"] },
  "application/x-subrip": { "source": "apache", "extensions": ["srt"] },
  "application/x-sv4cpio": { "source": "apache", "extensions": ["sv4cpio"] },
  "application/x-sv4crc": { "source": "apache", "extensions": ["sv4crc"] },
  "application/x-t3vm-image": { "source": "apache", "extensions": ["t3"] },
  "application/x-tads": { "source": "apache", "extensions": ["gam"] },
  "application/x-tar": { "source": "apache", "compressible": true, "extensions": ["tar"] },
  "application/x-tcl": { "source": "apache", "extensions": ["tcl", "tk"] },
  "application/x-tex": { "source": "apache", "extensions": ["tex"] },
  "application/x-tex-tfm": { "source": "apache", "extensions": ["tfm"] },
  "application/x-texinfo": { "source": "apache", "extensions": ["texinfo", "texi"] },
  "application/x-tgif": { "source": "apache", "extensions": ["obj"] },
  "application/x-ustar": { "source": "apache", "extensions": ["ustar"] },
  "application/x-virtualbox-hdd": { "compressible": true, "extensions": ["hdd"] },
  "application/x-virtualbox-ova": { "compressible": true, "extensions": ["ova"] },
  "application/x-virtualbox-ovf": { "compressible": true, "extensions": ["ovf"] },
  "application/x-virtualbox-vbox": { "compressible": true, "extensions": ["vbox"] },
  "application/x-virtualbox-vbox-extpack": { "compressible": false, "extensions": ["vbox-extpack"] },
  "application/x-virtualbox-vdi": { "compressible": true, "extensions": ["vdi"] },
  "application/x-virtualbox-vhd": { "compressible": true, "extensions": ["vhd"] },
  "application/x-virtualbox-vmdk": { "compressible": true, "extensions": ["vmdk"] },
  "application/x-wais-source": { "source": "apache", "extensions": ["src"] },
  "application/x-web-app-manifest+json": { "compressible": true, "extensions": ["webapp"] },
  "application/x-www-form-urlencoded": { "source": "iana", "compressible": true },
  "application/x-x509-ca-cert": { "source": "iana", "extensions": ["der", "crt", "pem"] },
  "application/x-x509-ca-ra-cert": { "source": "iana" },
  "application/x-x509-next-ca-cert": { "source": "iana" },
  "application/x-xfig": { "source": "apache", "extensions": ["fig"] },
  "application/x-xliff+xml": { "source": "apache", "compressible": true, "extensions": ["xlf"] },
  "application/x-xpinstall": { "source": "apache", "compressible": false, "extensions": ["xpi"] },
  "application/x-xz": { "source": "apache", "extensions": ["xz"] },
  "application/x-zmachine": { "source": "apache", "extensions": ["z1", "z2", "z3", "z4", "z5", "z6", "z7", "z8"] },
  "application/x400-bp": { "source": "iana" },
  "application/xacml+xml": { "source": "iana", "compressible": true },
  "application/xaml+xml": { "source": "apache", "compressible": true, "extensions": ["xaml"] },
  "application/xcap-att+xml": { "source": "iana", "compressible": true, "extensions": ["xav"] },
  "application/xcap-caps+xml": { "source": "iana", "compressible": true, "extensions": ["xca"] },
  "application/xcap-diff+xml": { "source": "iana", "compressible": true, "extensions": ["xdf"] },
  "application/xcap-el+xml": { "source": "iana", "compressible": true, "extensions": ["xel"] },
  "application/xcap-error+xml": { "source": "iana", "compressible": true },
  "application/xcap-ns+xml": { "source": "iana", "compressible": true, "extensions": ["xns"] },
  "application/xcon-conference-info+xml": { "source": "iana", "compressible": true },
  "application/xcon-conference-info-diff+xml": { "source": "iana", "compressible": true },
  "application/xenc+xml": { "source": "iana", "compressible": true, "extensions": ["xenc"] },
  "application/xhtml+xml": { "source": "iana", "compressible": true, "extensions": ["xhtml", "xht"] },
  "application/xhtml-voice+xml": { "source": "apache", "compressible": true },
  "application/xliff+xml": { "source": "iana", "compressible": true, "extensions": ["xlf"] },
  "application/xml": { "source": "iana", "compressible": true, "extensions": ["xml", "xsl", "xsd", "rng"] },
  "application/xml-dtd": { "source": "iana", "compressible": true, "extensions": ["dtd"] },
  "application/xml-external-parsed-entity": { "source": "iana" },
  "application/xml-patch+xml": { "source": "iana", "compressible": true },
  "application/xmpp+xml": { "source": "iana", "compressible": true },
  "application/xop+xml": { "source": "iana", "compressible": true, "extensions": ["xop"] },
  "application/xproc+xml": { "source": "apache", "compressible": true, "extensions": ["xpl"] },
  "application/xslt+xml": { "source": "iana", "compressible": true, "extensions": ["xsl", "xslt"] },
  "application/xspf+xml": { "source": "apache", "compressible": true, "extensions": ["xspf"] },
  "application/xv+xml": { "source": "iana", "compressible": true, "extensions": ["mxml", "xhvml", "xvml", "xvm"] },
  "application/yang": { "source": "iana", "extensions": ["yang"] },
  "application/yang-data+json": { "source": "iana", "compressible": true },
  "application/yang-data+xml": { "source": "iana", "compressible": true },
  "application/yang-patch+json": { "source": "iana", "compressible": true },
  "application/yang-patch+xml": { "source": "iana", "compressible": true },
  "application/yin+xml": { "source": "iana", "compressible": true, "extensions": ["yin"] },
  "application/zip": { "source": "iana", "compressible": false, "extensions": ["zip"] },
  "application/zlib": { "source": "iana" },
  "application/zstd": { "source": "iana" },
  "audio/1d-interleaved-parityfec": { "source": "iana" },
  "audio/32kadpcm": { "source": "iana" },
  "audio/3gpp": { "source": "iana", "compressible": false, "extensions": ["3gpp"] },
  "audio/3gpp2": { "source": "iana" },
  "audio/aac": { "source": "iana" },
  "audio/ac3": { "source": "iana" },
  "audio/adpcm": { "source": "apache", "extensions": ["adp"] },
  "audio/amr": { "source": "iana", "extensions": ["amr"] },
  "audio/amr-wb": { "source": "iana" },
  "audio/amr-wb+": { "source": "iana" },
  "audio/aptx": { "source": "iana" },
  "audio/asc": { "source": "iana" },
  "audio/atrac-advanced-lossless": { "source": "iana" },
  "audio/atrac-x": { "source": "iana" },
  "audio/atrac3": { "source": "iana" },
  "audio/basic": { "source": "iana", "compressible": false, "extensions": ["au", "snd"] },
  "audio/bv16": { "source": "iana" },
  "audio/bv32": { "source": "iana" },
  "audio/clearmode": { "source": "iana" },
  "audio/cn": { "source": "iana" },
  "audio/dat12": { "source": "iana" },
  "audio/dls": { "source": "iana" },
  "audio/dsr-es201108": { "source": "iana" },
  "audio/dsr-es202050": { "source": "iana" },
  "audio/dsr-es202211": { "source": "iana" },
  "audio/dsr-es202212": { "source": "iana" },
  "audio/dv": { "source": "iana" },
  "audio/dvi4": { "source": "iana" },
  "audio/eac3": { "source": "iana" },
  "audio/encaprtp": { "source": "iana" },
  "audio/evrc": { "source": "iana" },
  "audio/evrc-qcp": { "source": "iana" },
  "audio/evrc0": { "source": "iana" },
  "audio/evrc1": { "source": "iana" },
  "audio/evrcb": { "source": "iana" },
  "audio/evrcb0": { "source": "iana" },
  "audio/evrcb1": { "source": "iana" },
  "audio/evrcnw": { "source": "iana" },
  "audio/evrcnw0": { "source": "iana" },
  "audio/evrcnw1": { "source": "iana" },
  "audio/evrcwb": { "source": "iana" },
  "audio/evrcwb0": { "source": "iana" },
  "audio/evrcwb1": { "source": "iana" },
  "audio/evs": { "source": "iana" },
  "audio/flexfec": { "source": "iana" },
  "audio/fwdred": { "source": "iana" },
  "audio/g711-0": { "source": "iana" },
  "audio/g719": { "source": "iana" },
  "audio/g722": { "source": "iana" },
  "audio/g7221": { "source": "iana" },
  "audio/g723": { "source": "iana" },
  "audio/g726-16": { "source": "iana" },
  "audio/g726-24": { "source": "iana" },
  "audio/g726-32": { "source": "iana" },
  "audio/g726-40": { "source": "iana" },
  "audio/g728": { "source": "iana" },
  "audio/g729": { "source": "iana" },
  "audio/g7291": { "source": "iana" },
  "audio/g729d": { "source": "iana" },
  "audio/g729e": { "source": "iana" },
  "audio/gsm": { "source": "iana" },
  "audio/gsm-efr": { "source": "iana" },
  "audio/gsm-hr-08": { "source": "iana" },
  "audio/ilbc": { "source": "iana" },
  "audio/ip-mr_v2.5": { "source": "iana" },
  "audio/isac": { "source": "apache" },
  "audio/l16": { "source": "iana" },
  "audio/l20": { "source": "iana" },
  "audio/l24": { "source": "iana", "compressible": false },
  "audio/l8": { "source": "iana" },
  "audio/lpc": { "source": "iana" },
  "audio/melp": { "source": "iana" },
  "audio/melp1200": { "source": "iana" },
  "audio/melp2400": { "source": "iana" },
  "audio/melp600": { "source": "iana" },
  "audio/mhas": { "source": "iana" },
  "audio/midi": { "source": "apache", "extensions": ["mid", "midi", "kar", "rmi"] },
  "audio/mobile-xmf": { "source": "iana", "extensions": ["mxmf"] },
  "audio/mp3": { "compressible": false, "extensions": ["mp3"] },
  "audio/mp4": { "source": "iana", "compressible": false, "extensions": ["m4a", "mp4a"] },
  "audio/mp4a-latm": { "source": "iana" },
  "audio/mpa": { "source": "iana" },
  "audio/mpa-robust": { "source": "iana" },
  "audio/mpeg": { "source": "iana", "compressible": false, "extensions": ["mpga", "mp2", "mp2a", "mp3", "m2a", "m3a"] },
  "audio/mpeg4-generic": { "source": "iana" },
  "audio/musepack": { "source": "apache" },
  "audio/ogg": { "source": "iana", "compressible": false, "extensions": ["oga", "ogg", "spx", "opus"] },
  "audio/opus": { "source": "iana" },
  "audio/parityfec": { "source": "iana" },
  "audio/pcma": { "source": "iana" },
  "audio/pcma-wb": { "source": "iana" },
  "audio/pcmu": { "source": "iana" },
  "audio/pcmu-wb": { "source": "iana" },
  "audio/prs.sid": { "source": "iana" },
  "audio/qcelp": { "source": "iana" },
  "audio/raptorfec": { "source": "iana" },
  "audio/red": { "source": "iana" },
  "audio/rtp-enc-aescm128": { "source": "iana" },
  "audio/rtp-midi": { "source": "iana" },
  "audio/rtploopback": { "source": "iana" },
  "audio/rtx": { "source": "iana" },
  "audio/s3m": { "source": "apache", "extensions": ["s3m"] },
  "audio/scip": { "source": "iana" },
  "audio/silk": { "source": "apache", "extensions": ["sil"] },
  "audio/smv": { "source": "iana" },
  "audio/smv-qcp": { "source": "iana" },
  "audio/smv0": { "source": "iana" },
  "audio/sofa": { "source": "iana" },
  "audio/sp-midi": { "source": "iana" },
  "audio/speex": { "source": "iana" },
  "audio/t140c": { "source": "iana" },
  "audio/t38": { "source": "iana" },
  "audio/telephone-event": { "source": "iana" },
  "audio/tetra_acelp": { "source": "iana" },
  "audio/tetra_acelp_bb": { "source": "iana" },
  "audio/tone": { "source": "iana" },
  "audio/tsvcis": { "source": "iana" },
  "audio/uemclip": { "source": "iana" },
  "audio/ulpfec": { "source": "iana" },
  "audio/usac": { "source": "iana" },
  "audio/vdvi": { "source": "iana" },
  "audio/vmr-wb": { "source": "iana" },
  "audio/vnd.3gpp.iufp": { "source": "iana" },
  "audio/vnd.4sb": { "source": "iana" },
  "audio/vnd.audiokoz": { "source": "iana" },
  "audio/vnd.celp": { "source": "iana" },
  "audio/vnd.cisco.nse": { "source": "iana" },
  "audio/vnd.cmles.radio-events": { "source": "iana" },
  "audio/vnd.cns.anp1": { "source": "iana" },
  "audio/vnd.cns.inf1": { "source": "iana" },
  "audio/vnd.dece.audio": { "source": "iana", "extensions": ["uva", "uvva"] },
  "audio/vnd.digital-winds": { "source": "iana", "extensions": ["eol"] },
  "audio/vnd.dlna.adts": { "source": "iana" },
  "audio/vnd.dolby.heaac.1": { "source": "iana" },
  "audio/vnd.dolby.heaac.2": { "source": "iana" },
  "audio/vnd.dolby.mlp": { "source": "iana" },
  "audio/vnd.dolby.mps": { "source": "iana" },
  "audio/vnd.dolby.pl2": { "source": "iana" },
  "audio/vnd.dolby.pl2x": { "source": "iana" },
  "audio/vnd.dolby.pl2z": { "source": "iana" },
  "audio/vnd.dolby.pulse.1": { "source": "iana" },
  "audio/vnd.dra": { "source": "iana", "extensions": ["dra"] },
  "audio/vnd.dts": { "source": "iana", "extensions": ["dts"] },
  "audio/vnd.dts.hd": { "source": "iana", "extensions": ["dtshd"] },
  "audio/vnd.dts.uhd": { "source": "iana" },
  "audio/vnd.dvb.file": { "source": "iana" },
  "audio/vnd.everad.plj": { "source": "iana" },
  "audio/vnd.hns.audio": { "source": "iana" },
  "audio/vnd.lucent.voice": { "source": "iana", "extensions": ["lvp"] },
  "audio/vnd.ms-playready.media.pya": { "source": "iana", "extensions": ["pya"] },
  "audio/vnd.nokia.mobile-xmf": { "source": "iana" },
  "audio/vnd.nortel.vbk": { "source": "iana" },
  "audio/vnd.nuera.ecelp4800": { "source": "iana", "extensions": ["ecelp4800"] },
  "audio/vnd.nuera.ecelp7470": { "source": "iana", "extensions": ["ecelp7470"] },
  "audio/vnd.nuera.ecelp9600": { "source": "iana", "extensions": ["ecelp9600"] },
  "audio/vnd.octel.sbc": { "source": "iana" },
  "audio/vnd.presonus.multitrack": { "source": "iana" },
  "audio/vnd.qcelp": { "source": "iana" },
  "audio/vnd.rhetorex.32kadpcm": { "source": "iana" },
  "audio/vnd.rip": { "source": "iana", "extensions": ["rip"] },
  "audio/vnd.rn-realaudio": { "compressible": false },
  "audio/vnd.sealedmedia.softseal.mpeg": { "source": "iana" },
  "audio/vnd.vmx.cvsd": { "source": "iana" },
  "audio/vnd.wave": { "compressible": false },
  "audio/vorbis": { "source": "iana", "compressible": false },
  "audio/vorbis-config": { "source": "iana" },
  "audio/wav": { "compressible": false, "extensions": ["wav"] },
  "audio/wave": { "compressible": false, "extensions": ["wav"] },
  "audio/webm": { "source": "apache", "compressible": false, "extensions": ["weba"] },
  "audio/x-aac": { "source": "apache", "compressible": false, "extensions": ["aac"] },
  "audio/x-aiff": { "source": "apache", "extensions": ["aif", "aiff", "aifc"] },
  "audio/x-caf": { "source": "apache", "compressible": false, "extensions": ["caf"] },
  "audio/x-flac": { "source": "apache", "extensions": ["flac"] },
  "audio/x-m4a": { "source": "nginx", "extensions": ["m4a"] },
  "audio/x-matroska": { "source": "apache", "extensions": ["mka"] },
  "audio/x-mpegurl": { "source": "apache", "extensions": ["m3u"] },
  "audio/x-ms-wax": { "source": "apache", "extensions": ["wax"] },
  "audio/x-ms-wma": { "source": "apache", "extensions": ["wma"] },
  "audio/x-pn-realaudio": { "source": "apache", "extensions": ["ram", "ra"] },
  "audio/x-pn-realaudio-plugin": { "source": "apache", "extensions": ["rmp"] },
  "audio/x-realaudio": { "source": "nginx", "extensions": ["ra"] },
  "audio/x-tta": { "source": "apache" },
  "audio/x-wav": { "source": "apache", "extensions": ["wav"] },
  "audio/xm": { "source": "apache", "extensions": ["xm"] },
  "chemical/x-cdx": { "source": "apache", "extensions": ["cdx"] },
  "chemical/x-cif": { "source": "apache", "extensions": ["cif"] },
  "chemical/x-cmdf": { "source": "apache", "extensions": ["cmdf"] },
  "chemical/x-cml": { "source": "apache", "extensions": ["cml"] },
  "chemical/x-csml": { "source": "apache", "extensions": ["csml"] },
  "chemical/x-pdb": { "source": "apache" },
  "chemical/x-xyz": { "source": "apache", "extensions": ["xyz"] },
  "font/collection": { "source": "iana", "extensions": ["ttc"] },
  "font/otf": { "source": "iana", "compressible": true, "extensions": ["otf"] },
  "font/sfnt": { "source": "iana" },
  "font/ttf": { "source": "iana", "compressible": true, "extensions": ["ttf"] },
  "font/woff": { "source": "iana", "extensions": ["woff"] },
  "font/woff2": { "source": "iana", "extensions": ["woff2"] },
  "image/aces": { "source": "iana", "extensions": ["exr"] },
  "image/apng": { "compressible": false, "extensions": ["apng"] },
  "image/avci": { "source": "iana", "extensions": ["avci"] },
  "image/avcs": { "source": "iana", "extensions": ["avcs"] },
  "image/avif": { "source": "iana", "compressible": false, "extensions": ["avif"] },
  "image/bmp": { "source": "iana", "compressible": true, "extensions": ["bmp"] },
  "image/cgm": { "source": "iana", "extensions": ["cgm"] },
  "image/dicom-rle": { "source": "iana", "extensions": ["drle"] },
  "image/emf": { "source": "iana", "extensions": ["emf"] },
  "image/fits": { "source": "iana", "extensions": ["fits"] },
  "image/g3fax": { "source": "iana", "extensions": ["g3"] },
  "image/gif": { "source": "iana", "compressible": false, "extensions": ["gif"] },
  "image/heic": { "source": "iana", "extensions": ["heic"] },
  "image/heic-sequence": { "source": "iana", "extensions": ["heics"] },
  "image/heif": { "source": "iana", "extensions": ["heif"] },
  "image/heif-sequence": { "source": "iana", "extensions": ["heifs"] },
  "image/hej2k": { "source": "iana", "extensions": ["hej2"] },
  "image/hsj2": { "source": "iana", "extensions": ["hsj2"] },
  "image/ief": { "source": "iana", "extensions": ["ief"] },
  "image/jls": { "source": "iana", "extensions": ["jls"] },
  "image/jp2": { "source": "iana", "compressible": false, "extensions": ["jp2", "jpg2"] },
  "image/jpeg": { "source": "iana", "compressible": false, "extensions": ["jpeg", "jpg", "jpe"] },
  "image/jph": { "source": "iana", "extensions": ["jph"] },
  "image/jphc": { "source": "iana", "extensions": ["jhc"] },
  "image/jpm": { "source": "iana", "compressible": false, "extensions": ["jpm"] },
  "image/jpx": { "source": "iana", "compressible": false, "extensions": ["jpx", "jpf"] },
  "image/jxr": { "source": "iana", "extensions": ["jxr"] },
  "image/jxra": { "source": "iana", "extensions": ["jxra"] },
  "image/jxrs": { "source": "iana", "extensions": ["jxrs"] },
  "image/jxs": { "source": "iana", "extensions": ["jxs"] },
  "image/jxsc": { "source": "iana", "extensions": ["jxsc"] },
  "image/jxsi": { "source": "iana", "extensions": ["jxsi"] },
  "image/jxss": { "source": "iana", "extensions": ["jxss"] },
  "image/ktx": { "source": "iana", "extensions": ["ktx"] },
  "image/ktx2": { "source": "iana", "extensions": ["ktx2"] },
  "image/naplps": { "source": "iana" },
  "image/pjpeg": { "compressible": false },
  "image/png": { "source": "iana", "compressible": false, "extensions": ["png"] },
  "image/prs.btif": { "source": "iana", "extensions": ["btif"] },
  "image/prs.pti": { "source": "iana", "extensions": ["pti"] },
  "image/pwg-raster": { "source": "iana" },
  "image/sgi": { "source": "apache", "extensions": ["sgi"] },
  "image/svg+xml": { "source": "iana", "compressible": true, "extensions": ["svg", "svgz"] },
  "image/t38": { "source": "iana", "extensions": ["t38"] },
  "image/tiff": { "source": "iana", "compressible": false, "extensions": ["tif", "tiff"] },
  "image/tiff-fx": { "source": "iana", "extensions": ["tfx"] },
  "image/vnd.adobe.photoshop": { "source": "iana", "compressible": true, "extensions": ["psd"] },
  "image/vnd.airzip.accelerator.azv": { "source": "iana", "extensions": ["azv"] },
  "image/vnd.cns.inf2": { "source": "iana" },
  "image/vnd.dece.graphic": { "source": "iana", "extensions": ["uvi", "uvvi", "uvg", "uvvg"] },
  "image/vnd.djvu": { "source": "iana", "extensions": ["djvu", "djv"] },
  "image/vnd.dvb.subtitle": { "source": "iana", "extensions": ["sub"] },
  "image/vnd.dwg": { "source": "iana", "extensions": ["dwg"] },
  "image/vnd.dxf": { "source": "iana", "extensions": ["dxf"] },
  "image/vnd.fastbidsheet": { "source": "iana", "extensions": ["fbs"] },
  "image/vnd.fpx": { "source": "iana", "extensions": ["fpx"] },
  "image/vnd.fst": { "source": "iana", "extensions": ["fst"] },
  "image/vnd.fujixerox.edmics-mmr": { "source": "iana", "extensions": ["mmr"] },
  "image/vnd.fujixerox.edmics-rlc": { "source": "iana", "extensions": ["rlc"] },
  "image/vnd.globalgraphics.pgb": { "source": "iana" },
  "image/vnd.microsoft.icon": { "source": "iana", "compressible": true, "extensions": ["ico"] },
  "image/vnd.mix": { "source": "iana" },
  "image/vnd.mozilla.apng": { "source": "iana" },
  "image/vnd.ms-dds": { "compressible": true, "extensions": ["dds"] },
  "image/vnd.ms-modi": { "source": "iana", "extensions": ["mdi"] },
  "image/vnd.ms-photo": { "source": "apache", "extensions": ["wdp"] },
  "image/vnd.net-fpx": { "source": "iana", "extensions": ["npx"] },
  "image/vnd.pco.b16": { "source": "iana", "extensions": ["b16"] },
  "image/vnd.radiance": { "source": "iana" },
  "image/vnd.sealed.png": { "source": "iana" },
  "image/vnd.sealedmedia.softseal.gif": { "source": "iana" },
  "image/vnd.sealedmedia.softseal.jpg": { "source": "iana" },
  "image/vnd.svf": { "source": "iana" },
  "image/vnd.tencent.tap": { "source": "iana", "extensions": ["tap"] },
  "image/vnd.valve.source.texture": { "source": "iana", "extensions": ["vtf"] },
  "image/vnd.wap.wbmp": { "source": "iana", "extensions": ["wbmp"] },
  "image/vnd.xiff": { "source": "iana", "extensions": ["xif"] },
  "image/vnd.zbrush.pcx": { "source": "iana", "extensions": ["pcx"] },
  "image/webp": { "source": "apache", "extensions": ["webp"] },
  "image/wmf": { "source": "iana", "extensions": ["wmf"] },
  "image/x-3ds": { "source": "apache", "extensions": ["3ds"] },
  "image/x-cmu-raster": { "source": "apache", "extensions": ["ras"] },
  "image/x-cmx": { "source": "apache", "extensions": ["cmx"] },
  "image/x-freehand": { "source": "apache", "extensions": ["fh", "fhc", "fh4", "fh5", "fh7"] },
  "image/x-icon": { "source": "apache", "compressible": true, "extensions": ["ico"] },
  "image/x-jng": { "source": "nginx", "extensions": ["jng"] },
  "image/x-mrsid-image": { "source": "apache", "extensions": ["sid"] },
  "image/x-ms-bmp": { "source": "nginx", "compressible": true, "extensions": ["bmp"] },
  "image/x-pcx": { "source": "apache", "extensions": ["pcx"] },
  "image/x-pict": { "source": "apache", "extensions": ["pic", "pct"] },
  "image/x-portable-anymap": { "source": "apache", "extensions": ["pnm"] },
  "image/x-portable-bitmap": { "source": "apache", "extensions": ["pbm"] },
  "image/x-portable-graymap": { "source": "apache", "extensions": ["pgm"] },
  "image/x-portable-pixmap": { "source": "apache", "extensions": ["ppm"] },
  "image/x-rgb": { "source": "apache", "extensions": ["rgb"] },
  "image/x-tga": { "source": "apache", "extensions": ["tga"] },
  "image/x-xbitmap": { "source": "apache", "extensions": ["xbm"] },
  "image/x-xcf": { "compressible": false },
  "image/x-xpixmap": { "source": "apache", "extensions": ["xpm"] },
  "image/x-xwindowdump": { "source": "apache", "extensions": ["xwd"] },
  "message/cpim": { "source": "iana" },
  "message/delivery-status": { "source": "iana" },
  "message/disposition-notification": { "source": "iana", "extensions": ["disposition-notification"] },
  "message/external-body": { "source": "iana" },
  "message/feedback-report": { "source": "iana" },
  "message/global": { "source": "iana", "extensions": ["u8msg"] },
  "message/global-delivery-status": { "source": "iana", "extensions": ["u8dsn"] },
  "message/global-disposition-notification": { "source": "iana", "extensions": ["u8mdn"] },
  "message/global-headers": { "source": "iana", "extensions": ["u8hdr"] },
  "message/http": { "source": "iana", "compressible": false },
  "message/imdn+xml": { "source": "iana", "compressible": true },
  "message/news": { "source": "iana" },
  "message/partial": { "source": "iana", "compressible": false },
  "message/rfc822": { "source": "iana", "compressible": true, "extensions": ["eml", "mime"] },
  "message/s-http": { "source": "iana" },
  "message/sip": { "source": "iana" },
  "message/sipfrag": { "source": "iana" },
  "message/tracking-status": { "source": "iana" },
  "message/vnd.si.simp": { "source": "iana" },
  "message/vnd.wfa.wsc": { "source": "iana", "extensions": ["wsc"] },
  "model/3mf": { "source": "iana", "extensions": ["3mf"] },
  "model/e57": { "source": "iana" },
  "model/gltf+json": { "source": "iana", "compressible": true, "extensions": ["gltf"] },
  "model/gltf-binary": { "source": "iana", "compressible": true, "extensions": ["glb"] },
  "model/iges": { "source": "iana", "compressible": false, "extensions": ["igs", "iges"] },
  "model/mesh": { "source": "iana", "compressible": false, "extensions": ["msh", "mesh", "silo"] },
  "model/mtl": { "source": "iana", "extensions": ["mtl"] },
  "model/obj": { "source": "iana", "extensions": ["obj"] },
  "model/step": { "source": "iana" },
  "model/step+xml": { "source": "iana", "compressible": true, "extensions": ["stpx"] },
  "model/step+zip": { "source": "iana", "compressible": false, "extensions": ["stpz"] },
  "model/step-xml+zip": { "source": "iana", "compressible": false, "extensions": ["stpxz"] },
  "model/stl": { "source": "iana", "extensions": ["stl"] },
  "model/vnd.collada+xml": { "source": "iana", "compressible": true, "extensions": ["dae"] },
  "model/vnd.dwf": { "source": "iana", "extensions": ["dwf"] },
  "model/vnd.flatland.3dml": { "source": "iana" },
  "model/vnd.gdl": { "source": "iana", "extensions": ["gdl"] },
  "model/vnd.gs-gdl": { "source": "apache" },
  "model/vnd.gs.gdl": { "source": "iana" },
  "model/vnd.gtw": { "source": "iana", "extensions": ["gtw"] },
  "model/vnd.moml+xml": { "source": "iana", "compressible": true },
  "model/vnd.mts": { "source": "iana", "extensions": ["mts"] },
  "model/vnd.opengex": { "source": "iana", "extensions": ["ogex"] },
  "model/vnd.parasolid.transmit.binary": { "source": "iana", "extensions": ["x_b"] },
  "model/vnd.parasolid.transmit.text": { "source": "iana", "extensions": ["x_t"] },
  "model/vnd.pytha.pyox": { "source": "iana" },
  "model/vnd.rosette.annotated-data-model": { "source": "iana" },
  "model/vnd.sap.vds": { "source": "iana", "extensions": ["vds"] },
  "model/vnd.usdz+zip": { "source": "iana", "compressible": false, "extensions": ["usdz"] },
  "model/vnd.valve.source.compiled-map": { "source": "iana", "extensions": ["bsp"] },
  "model/vnd.vtu": { "source": "iana", "extensions": ["vtu"] },
  "model/vrml": { "source": "iana", "compressible": false, "extensions": ["wrl", "vrml"] },
  "model/x3d+binary": { "source": "apache", "compressible": false, "extensions": ["x3db", "x3dbz"] },
  "model/x3d+fastinfoset": { "source": "iana", "extensions": ["x3db"] },
  "model/x3d+vrml": { "source": "apache", "compressible": false, "extensions": ["x3dv", "x3dvz"] },
  "model/x3d+xml": { "source": "iana", "compressible": true, "extensions": ["x3d", "x3dz"] },
  "model/x3d-vrml": { "source": "iana", "extensions": ["x3dv"] },
  "multipart/alternative": { "source": "iana", "compressible": false },
  "multipart/appledouble": { "source": "iana" },
  "multipart/byteranges": { "source": "iana" },
  "multipart/digest": { "source": "iana" },
  "multipart/encrypted": { "source": "iana", "compressible": false },
  "multipart/form-data": { "source": "iana", "compressible": false },
  "multipart/header-set": { "source": "iana" },
  "multipart/mixed": { "source": "iana" },
  "multipart/multilingual": { "source": "iana" },
  "multipart/parallel": { "source": "iana" },
  "multipart/related": { "source": "iana", "compressible": false },
  "multipart/report": { "source": "iana" },
  "multipart/signed": { "source": "iana", "compressible": false },
  "multipart/vnd.bint.med-plus": { "source": "iana" },
  "multipart/voice-message": { "source": "iana" },
  "multipart/x-mixed-replace": { "source": "iana" },
  "text/1d-interleaved-parityfec": { "source": "iana" },
  "text/cache-manifest": { "source": "iana", "compressible": true, "extensions": ["appcache", "manifest"] },
  "text/calendar": { "source": "iana", "extensions": ["ics", "ifb"] },
  "text/calender": { "compressible": true },
  "text/cmd": { "compressible": true },
  "text/coffeescript": { "extensions": ["coffee", "litcoffee"] },
  "text/cql": { "source": "iana" },
  "text/cql-expression": { "source": "iana" },
  "text/cql-identifier": { "source": "iana" },
  "text/css": { "source": "iana", "charset": "UTF-8", "compressible": true, "extensions": ["css"] },
  "text/csv": { "source": "iana", "compressible": true, "extensions": ["csv"] },
  "text/csv-schema": { "source": "iana" },
  "text/directory": { "source": "iana" },
  "text/dns": { "source": "iana" },
  "text/ecmascript": { "source": "iana" },
  "text/encaprtp": { "source": "iana" },
  "text/enriched": { "source": "iana" },
  "text/fhirpath": { "source": "iana" },
  "text/flexfec": { "source": "iana" },
  "text/fwdred": { "source": "iana" },
  "text/gff3": { "source": "iana" },
  "text/grammar-ref-list": { "source": "iana" },
  "text/html": { "source": "iana", "compressible": true, "extensions": ["html", "htm", "shtml"] },
  "text/jade": { "extensions": ["jade"] },
  "text/javascript": { "source": "iana", "compressible": true },
  "text/jcr-cnd": { "source": "iana" },
  "text/jsx": { "compressible": true, "extensions": ["jsx"] },
  "text/less": { "compressible": true, "extensions": ["less"] },
  "text/markdown": { "source": "iana", "compressible": true, "extensions": ["markdown", "md"] },
  "text/mathml": { "source": "nginx", "extensions": ["mml"] },
  "text/mdx": { "compressible": true, "extensions": ["mdx"] },
  "text/mizar": { "source": "iana" },
  "text/n3": { "source": "iana", "charset": "UTF-8", "compressible": true, "extensions": ["n3"] },
  "text/parameters": { "source": "iana", "charset": "UTF-8" },
  "text/parityfec": { "source": "iana" },
  "text/plain": { "source": "iana", "compressible": true, "extensions": ["txt", "text", "conf", "def", "list", "log", "in", "ini"] },
  "text/provenance-notation": { "source": "iana", "charset": "UTF-8" },
  "text/prs.fallenstein.rst": { "source": "iana" },
  "text/prs.lines.tag": { "source": "iana", "extensions": ["dsc"] },
  "text/prs.prop.logic": { "source": "iana" },
  "text/raptorfec": { "source": "iana" },
  "text/red": { "source": "iana" },
  "text/rfc822-headers": { "source": "iana" },
  "text/richtext": { "source": "iana", "compressible": true, "extensions": ["rtx"] },
  "text/rtf": { "source": "iana", "compressible": true, "extensions": ["rtf"] },
  "text/rtp-enc-aescm128": { "source": "iana" },
  "text/rtploopback": { "source": "iana" },
  "text/rtx": { "source": "iana" },
  "text/sgml": { "source": "iana", "extensions": ["sgml", "sgm"] },
  "text/shaclc": { "source": "iana" },
  "text/shex": { "source": "iana", "extensions": ["shex"] },
  "text/slim": { "extensions": ["slim", "slm"] },
  "text/spdx": { "source": "iana", "extensions": ["spdx"] },
  "text/strings": { "source": "iana" },
  "text/stylus": { "extensions": ["stylus", "styl"] },
  "text/t140": { "source": "iana" },
  "text/tab-separated-values": { "source": "iana", "compressible": true, "extensions": ["tsv"] },
  "text/troff": { "source": "iana", "extensions": ["t", "tr", "roff", "man", "me", "ms"] },
  "text/turtle": { "source": "iana", "charset": "UTF-8", "extensions": ["ttl"] },
  "text/ulpfec": { "source": "iana" },
  "text/uri-list": { "source": "iana", "compressible": true, "extensions": ["uri", "uris", "urls"] },
  "text/vcard": { "source": "iana", "compressible": true, "extensions": ["vcard"] },
  "text/vnd.a": { "source": "iana" },
  "text/vnd.abc": { "source": "iana" },
  "text/vnd.ascii-art": { "source": "iana" },
  "text/vnd.curl": { "source": "iana", "extensions": ["curl"] },
  "text/vnd.curl.dcurl": { "source": "apache", "extensions": ["dcurl"] },
  "text/vnd.curl.mcurl": { "source": "apache", "extensions": ["mcurl"] },
  "text/vnd.curl.scurl": { "source": "apache", "extensions": ["scurl"] },
  "text/vnd.debian.copyright": { "source": "iana", "charset": "UTF-8" },
  "text/vnd.dmclientscript": { "source": "iana" },
  "text/vnd.dvb.subtitle": { "source": "iana", "extensions": ["sub"] },
  "text/vnd.esmertec.theme-descriptor": { "source": "iana", "charset": "UTF-8" },
  "text/vnd.familysearch.gedcom": { "source": "iana", "extensions": ["ged"] },
  "text/vnd.ficlab.flt": { "source": "iana" },
  "text/vnd.fly": { "source": "iana", "extensions": ["fly"] },
  "text/vnd.fmi.flexstor": { "source": "iana", "extensions": ["flx"] },
  "text/vnd.gml": { "source": "iana" },
  "text/vnd.graphviz": { "source": "iana", "extensions": ["gv"] },
  "text/vnd.hans": { "source": "iana" },
  "text/vnd.hgl": { "source": "iana" },
  "text/vnd.in3d.3dml": { "source": "iana", "extensions": ["3dml"] },
  "text/vnd.in3d.spot": { "source": "iana", "extensions": ["spot"] },
  "text/vnd.iptc.newsml": { "source": "iana" },
  "text/vnd.iptc.nitf": { "source": "iana" },
  "text/vnd.latex-z": { "source": "iana" },
  "text/vnd.motorola.reflex": { "source": "iana" },
  "text/vnd.ms-mediapackage": { "source": "iana" },
  "text/vnd.net2phone.commcenter.command": { "source": "iana" },
  "text/vnd.radisys.msml-basic-layout": { "source": "iana" },
  "text/vnd.senx.warpscript": { "source": "iana" },
  "text/vnd.si.uricatalogue": { "source": "iana" },
  "text/vnd.sosi": { "source": "iana" },
  "text/vnd.sun.j2me.app-descriptor": { "source": "iana", "charset": "UTF-8", "extensions": ["jad"] },
  "text/vnd.trolltech.linguist": { "source": "iana", "charset": "UTF-8" },
  "text/vnd.wap.si": { "source": "iana" },
  "text/vnd.wap.sl": { "source": "iana" },
  "text/vnd.wap.wml": { "source": "iana", "extensions": ["wml"] },
  "text/vnd.wap.wmlscript": { "source": "iana", "extensions": ["wmls"] },
  "text/vtt": { "source": "iana", "charset": "UTF-8", "compressible": true, "extensions": ["vtt"] },
  "text/x-asm": { "source": "apache", "extensions": ["s", "asm"] },
  "text/x-c": { "source": "apache", "extensions": ["c", "cc", "cxx", "cpp", "h", "hh", "dic"] },
  "text/x-component": { "source": "nginx", "extensions": ["htc"] },
  "text/x-fortran": { "source": "apache", "extensions": ["f", "for", "f77", "f90"] },
  "text/x-gwt-rpc": { "compressible": true },
  "text/x-handlebars-template": { "extensions": ["hbs"] },
  "text/x-java-source": { "source": "apache", "extensions": ["java"] },
  "text/x-jquery-tmpl": { "compressible": true },
  "text/x-lua": { "extensions": ["lua"] },
  "text/x-markdown": { "compressible": true, "extensions": ["mkd"] },
  "text/x-nfo": { "source": "apache", "extensions": ["nfo"] },
  "text/x-opml": { "source": "apache", "extensions": ["opml"] },
  "text/x-org": { "compressible": true, "extensions": ["org"] },
  "text/x-pascal": { "source": "apache", "extensions": ["p", "pas"] },
  "text/x-processing": { "compressible": true, "extensions": ["pde"] },
  "text/x-sass": { "extensions": ["sass"] },
  "text/x-scss": { "extensions": ["scss"] },
  "text/x-setext": { "source": "apache", "extensions": ["etx"] },
  "text/x-sfv": { "source": "apache", "extensions": ["sfv"] },
  "text/x-suse-ymp": { "compressible": true, "extensions": ["ymp"] },
  "text/x-uuencode": { "source": "apache", "extensions": ["uu"] },
  "text/x-vcalendar": { "source": "apache", "extensions": ["vcs"] },
  "text/x-vcard": { "source": "apache", "extensions": ["vcf"] },
  "text/xml": { "source": "iana", "compressible": true, "extensions": ["xml"] },
  "text/xml-external-parsed-entity": { "source": "iana" },
  "text/yaml": { "compressible": true, "extensions": ["yaml", "yml"] },
  "video/1d-interleaved-parityfec": { "source": "iana" },
  "video/3gpp": { "source": "iana", "extensions": ["3gp", "3gpp"] },
  "video/3gpp-tt": { "source": "iana" },
  "video/3gpp2": { "source": "iana", "extensions": ["3g2"] },
  "video/av1": { "source": "iana" },
  "video/bmpeg": { "source": "iana" },
  "video/bt656": { "source": "iana" },
  "video/celb": { "source": "iana" },
  "video/dv": { "source": "iana" },
  "video/encaprtp": { "source": "iana" },
  "video/ffv1": { "source": "iana" },
  "video/flexfec": { "source": "iana" },
  "video/h261": { "source": "iana", "extensions": ["h261"] },
  "video/h263": { "source": "iana", "extensions": ["h263"] },
  "video/h263-1998": { "source": "iana" },
  "video/h263-2000": { "source": "iana" },
  "video/h264": { "source": "iana", "extensions": ["h264"] },
  "video/h264-rcdo": { "source": "iana" },
  "video/h264-svc": { "source": "iana" },
  "video/h265": { "source": "iana" },
  "video/iso.segment": { "source": "iana", "extensions": ["m4s"] },
  "video/jpeg": { "source": "iana", "extensions": ["jpgv"] },
  "video/jpeg2000": { "source": "iana" },
  "video/jpm": { "source": "apache", "extensions": ["jpm", "jpgm"] },
  "video/jxsv": { "source": "iana" },
  "video/mj2": { "source": "iana", "extensions": ["mj2", "mjp2"] },
  "video/mp1s": { "source": "iana" },
  "video/mp2p": { "source": "iana" },
  "video/mp2t": { "source": "iana", "extensions": ["ts"] },
  "video/mp4": { "source": "iana", "compressible": false, "extensions": ["mp4", "mp4v", "mpg4"] },
  "video/mp4v-es": { "source": "iana" },
  "video/mpeg": { "source": "iana", "compressible": false, "extensions": ["mpeg", "mpg", "mpe", "m1v", "m2v"] },
  "video/mpeg4-generic": { "source": "iana" },
  "video/mpv": { "source": "iana" },
  "video/nv": { "source": "iana" },
  "video/ogg": { "source": "iana", "compressible": false, "extensions": ["ogv"] },
  "video/parityfec": { "source": "iana" },
  "video/pointer": { "source": "iana" },
  "video/quicktime": { "source": "iana", "compressible": false, "extensions": ["qt", "mov"] },
  "video/raptorfec": { "source": "iana" },
  "video/raw": { "source": "iana" },
  "video/rtp-enc-aescm128": { "source": "iana" },
  "video/rtploopback": { "source": "iana" },
  "video/rtx": { "source": "iana" },
  "video/scip": { "source": "iana" },
  "video/smpte291": { "source": "iana" },
  "video/smpte292m": { "source": "iana" },
  "video/ulpfec": { "source": "iana" },
  "video/vc1": { "source": "iana" },
  "video/vc2": { "source": "iana" },
  "video/vnd.cctv": { "source": "iana" },
  "video/vnd.dece.hd": { "source": "iana", "extensions": ["uvh", "uvvh"] },
  "video/vnd.dece.mobile": { "source": "iana", "extensions": ["uvm", "uvvm"] },
  "video/vnd.dece.mp4": { "source": "iana" },
  "video/vnd.dece.pd": { "source": "iana", "extensions": ["uvp", "uvvp"] },
  "video/vnd.dece.sd": { "source": "iana", "extensions": ["uvs", "uvvs"] },
  "video/vnd.dece.video": { "source": "iana", "extensions": ["uvv", "uvvv"] },
  "video/vnd.directv.mpeg": { "source": "iana" },
  "video/vnd.directv.mpeg-tts": { "source": "iana" },
  "video/vnd.dlna.mpeg-tts": { "source": "iana" },
  "video/vnd.dvb.file": { "source": "iana", "extensions": ["dvb"] },
  "video/vnd.fvt": { "source": "iana", "extensions": ["fvt"] },
  "video/vnd.hns.video": { "source": "iana" },
  "video/vnd.iptvforum.1dparityfec-1010": { "source": "iana" },
  "video/vnd.iptvforum.1dparityfec-2005": { "source": "iana" },
  "video/vnd.iptvforum.2dparityfec-1010": { "source": "iana" },
  "video/vnd.iptvforum.2dparityfec-2005": { "source": "iana" },
  "video/vnd.iptvforum.ttsavc": { "source": "iana" },
  "video/vnd.iptvforum.ttsmpeg2": { "source": "iana" },
  "video/vnd.motorola.video": { "source": "iana" },
  "video/vnd.motorola.videop": { "source": "iana" },
  "video/vnd.mpegurl": { "source": "iana", "extensions": ["mxu", "m4u"] },
  "video/vnd.ms-playready.media.pyv": { "source": "iana", "extensions": ["pyv"] },
  "video/vnd.nokia.interleaved-multimedia": { "source": "iana" },
  "video/vnd.nokia.mp4vr": { "source": "iana" },
  "video/vnd.nokia.videovoip": { "source": "iana" },
  "video/vnd.objectvideo": { "source": "iana" },
  "video/vnd.radgamettools.bink": { "source": "iana" },
  "video/vnd.radgamettools.smacker": { "source": "iana" },
  "video/vnd.sealed.mpeg1": { "source": "iana" },
  "video/vnd.sealed.mpeg4": { "source": "iana" },
  "video/vnd.sealed.swf": { "source": "iana" },
  "video/vnd.sealedmedia.softseal.mov": { "source": "iana" },
  "video/vnd.uvvu.mp4": { "source": "iana", "extensions": ["uvu", "uvvu"] },
  "video/vnd.vivo": { "source": "iana", "extensions": ["viv"] },
  "video/vnd.youtube.yt": { "source": "iana" },
  "video/vp8": { "source": "iana" },
  "video/vp9": { "source": "iana" },
  "video/webm": { "source": "apache", "compressible": false, "extensions": ["webm"] },
  "video/x-f4v": { "source": "apache", "extensions": ["f4v"] },
  "video/x-fli": { "source": "apache", "extensions": ["fli"] },
  "video/x-flv": { "source": "apache", "compressible": false, "extensions": ["flv"] },
  "video/x-m4v": { "source": "apache", "extensions": ["m4v"] },
  "video/x-matroska": { "source": "apache", "compressible": false, "extensions": ["mkv", "mk3d", "mks"] },
  "video/x-mng": { "source": "apache", "extensions": ["mng"] },
  "video/x-ms-asf": { "source": "apache", "extensions": ["asf", "asx"] },
  "video/x-ms-vob": { "source": "apache", "extensions": ["vob"] },
  "video/x-ms-wm": { "source": "apache", "extensions": ["wm"] },
  "video/x-ms-wmv": { "source": "apache", "compressible": false, "extensions": ["wmv"] },
  "video/x-ms-wmx": { "source": "apache", "extensions": ["wmx"] },
  "video/x-ms-wvx": { "source": "apache", "extensions": ["wvx"] },
  "video/x-msvideo": { "source": "apache", "extensions": ["avi"] },
  "video/x-sgi-movie": { "source": "apache", "extensions": ["movie"] },
  "video/x-smv": { "source": "apache", "extensions": ["smv"] },
  "x-conference/x-cooltalk": { "source": "apache", "extensions": ["ice"] },
  "x-shader/x-fragment": { "compressible": true },
  "x-shader/x-vertex": { "compressible": true }
};
var mimeDb;
var hasRequiredMimeDb;
function requireMimeDb() {
  if (hasRequiredMimeDb) return mimeDb;
  hasRequiredMimeDb = 1;
  mimeDb = require$$0$1;
  return mimeDb;
}
var hasRequiredMimeTypes;
function requireMimeTypes() {
  if (hasRequiredMimeTypes) return mimeTypes;
  hasRequiredMimeTypes = 1;
  (function(exports$1) {
    var db = requireMimeDb();
    var extname = require$$1$1.extname;
    var EXTRACT_TYPE_REGEXP = /^\s*([^;\s]*)(?:;|\s|$)/;
    var TEXT_TYPE_REGEXP = /^text\//i;
    exports$1.charset = charset;
    exports$1.charsets = { lookup: charset };
    exports$1.contentType = contentType;
    exports$1.extension = extension;
    exports$1.extensions = /* @__PURE__ */ Object.create(null);
    exports$1.lookup = lookup;
    exports$1.types = /* @__PURE__ */ Object.create(null);
    populateMaps(exports$1.extensions, exports$1.types);
    function charset(type2) {
      if (!type2 || typeof type2 !== "string") {
        return false;
      }
      var match = EXTRACT_TYPE_REGEXP.exec(type2);
      var mime = match && db[match[1].toLowerCase()];
      if (mime && mime.charset) {
        return mime.charset;
      }
      if (match && TEXT_TYPE_REGEXP.test(match[1])) {
        return "UTF-8";
      }
      return false;
    }
    function contentType(str) {
      if (!str || typeof str !== "string") {
        return false;
      }
      var mime = str.indexOf("/") === -1 ? exports$1.lookup(str) : str;
      if (!mime) {
        return false;
      }
      if (mime.indexOf("charset") === -1) {
        var charset2 = exports$1.charset(mime);
        if (charset2) mime += "; charset=" + charset2.toLowerCase();
      }
      return mime;
    }
    function extension(type2) {
      if (!type2 || typeof type2 !== "string") {
        return false;
      }
      var match = EXTRACT_TYPE_REGEXP.exec(type2);
      var exts = match && exports$1.extensions[match[1].toLowerCase()];
      if (!exts || !exts.length) {
        return false;
      }
      return exts[0];
    }
    function lookup(path) {
      if (!path || typeof path !== "string") {
        return false;
      }
      var extension2 = extname("x." + path).toLowerCase().substr(1);
      if (!extension2) {
        return false;
      }
      return exports$1.types[extension2] || false;
    }
    function populateMaps(extensions, types) {
      var preference = ["nginx", "apache", void 0, "iana"];
      Object.keys(db).forEach(function forEachMimeType(type2) {
        var mime = db[type2];
        var exts = mime.extensions;
        if (!exts || !exts.length) {
          return;
        }
        extensions[type2] = exts;
        for (var i = 0; i < exts.length; i++) {
          var extension2 = exts[i];
          if (types[extension2]) {
            var from = preference.indexOf(db[types[extension2]].source);
            var to = preference.indexOf(mime.source);
            if (types[extension2] !== "application/octet-stream" && (from > to || from === to && types[extension2].substr(0, 12) === "application/")) {
              continue;
            }
          }
          types[extension2] = type2;
        }
      });
    }
  })(mimeTypes);
  return mimeTypes;
}
var defer_1;
var hasRequiredDefer;
function requireDefer() {
  if (hasRequiredDefer) return defer_1;
  hasRequiredDefer = 1;
  defer_1 = defer;
  function defer(fn) {
    var nextTick = typeof setImmediate == "function" ? setImmediate : typeof process == "object" && typeof process.nextTick == "function" ? process.nextTick : null;
    if (nextTick) {
      nextTick(fn);
    } else {
      setTimeout(fn, 0);
    }
  }
  return defer_1;
}
var async_1;
var hasRequiredAsync;
function requireAsync() {
  if (hasRequiredAsync) return async_1;
  hasRequiredAsync = 1;
  var defer = requireDefer();
  async_1 = async;
  function async(callback) {
    var isAsync = false;
    defer(function() {
      isAsync = true;
    });
    return function async_callback(err, result) {
      if (isAsync) {
        callback(err, result);
      } else {
        defer(function nextTick_callback() {
          callback(err, result);
        });
      }
    };
  }
  return async_1;
}
var abort_1;
var hasRequiredAbort;
function requireAbort() {
  if (hasRequiredAbort) return abort_1;
  hasRequiredAbort = 1;
  abort_1 = abort;
  function abort(state) {
    Object.keys(state.jobs).forEach(clean.bind(state));
    state.jobs = {};
  }
  function clean(key) {
    if (typeof this.jobs[key] == "function") {
      this.jobs[key]();
    }
  }
  return abort_1;
}
var iterate_1;
var hasRequiredIterate;
function requireIterate() {
  if (hasRequiredIterate) return iterate_1;
  hasRequiredIterate = 1;
  var async = requireAsync(), abort = requireAbort();
  iterate_1 = iterate;
  function iterate(list, iterator2, state, callback) {
    var key = state["keyedList"] ? state["keyedList"][state.index] : state.index;
    state.jobs[key] = runJob(iterator2, key, list[key], function(error, output) {
      if (!(key in state.jobs)) {
        return;
      }
      delete state.jobs[key];
      if (error) {
        abort(state);
      } else {
        state.results[key] = output;
      }
      callback(error, state.results);
    });
  }
  function runJob(iterator2, key, item, callback) {
    var aborter;
    if (iterator2.length == 2) {
      aborter = iterator2(item, async(callback));
    } else {
      aborter = iterator2(item, key, async(callback));
    }
    return aborter;
  }
  return iterate_1;
}
var state_1;
var hasRequiredState;
function requireState() {
  if (hasRequiredState) return state_1;
  hasRequiredState = 1;
  state_1 = state;
  function state(list, sortMethod) {
    var isNamedList = !Array.isArray(list), initState = {
      index: 0,
      keyedList: isNamedList || sortMethod ? Object.keys(list) : null,
      jobs: {},
      results: isNamedList ? {} : [],
      size: isNamedList ? Object.keys(list).length : list.length
    };
    if (sortMethod) {
      initState.keyedList.sort(isNamedList ? sortMethod : function(a, b) {
        return sortMethod(list[a], list[b]);
      });
    }
    return initState;
  }
  return state_1;
}
var terminator_1;
var hasRequiredTerminator;
function requireTerminator() {
  if (hasRequiredTerminator) return terminator_1;
  hasRequiredTerminator = 1;
  var abort = requireAbort(), async = requireAsync();
  terminator_1 = terminator;
  function terminator(callback) {
    if (!Object.keys(this.jobs).length) {
      return;
    }
    this.index = this.size;
    abort(this);
    async(callback)(null, this.results);
  }
  return terminator_1;
}
var parallel_1;
var hasRequiredParallel;
function requireParallel() {
  if (hasRequiredParallel) return parallel_1;
  hasRequiredParallel = 1;
  var iterate = requireIterate(), initState = requireState(), terminator = requireTerminator();
  parallel_1 = parallel;
  function parallel(list, iterator2, callback) {
    var state = initState(list);
    while (state.index < (state["keyedList"] || list).length) {
      iterate(list, iterator2, state, function(error, result) {
        if (error) {
          callback(error, result);
          return;
        }
        if (Object.keys(state.jobs).length === 0) {
          callback(null, state.results);
          return;
        }
      });
      state.index++;
    }
    return terminator.bind(state, callback);
  }
  return parallel_1;
}
var serialOrdered = { exports: {} };
var hasRequiredSerialOrdered;
function requireSerialOrdered() {
  if (hasRequiredSerialOrdered) return serialOrdered.exports;
  hasRequiredSerialOrdered = 1;
  var iterate = requireIterate(), initState = requireState(), terminator = requireTerminator();
  serialOrdered.exports = serialOrdered$1;
  serialOrdered.exports.ascending = ascending;
  serialOrdered.exports.descending = descending;
  function serialOrdered$1(list, iterator2, sortMethod, callback) {
    var state = initState(list, sortMethod);
    iterate(list, iterator2, state, function iteratorHandler(error, result) {
      if (error) {
        callback(error, result);
        return;
      }
      state.index++;
      if (state.index < (state["keyedList"] || list).length) {
        iterate(list, iterator2, state, iteratorHandler);
        return;
      }
      callback(null, state.results);
    });
    return terminator.bind(state, callback);
  }
  function ascending(a, b) {
    return a < b ? -1 : a > b ? 1 : 0;
  }
  function descending(a, b) {
    return -1 * ascending(a, b);
  }
  return serialOrdered.exports;
}
var serial_1;
var hasRequiredSerial;
function requireSerial() {
  if (hasRequiredSerial) return serial_1;
  hasRequiredSerial = 1;
  var serialOrdered2 = requireSerialOrdered();
  serial_1 = serial;
  function serial(list, iterator2, callback) {
    return serialOrdered2(list, iterator2, null, callback);
  }
  return serial_1;
}
var asynckit;
var hasRequiredAsynckit;
function requireAsynckit() {
  if (hasRequiredAsynckit) return asynckit;
  hasRequiredAsynckit = 1;
  asynckit = {
    parallel: requireParallel(),
    serial: requireSerial(),
    serialOrdered: requireSerialOrdered()
  };
  return asynckit;
}
var esObjectAtoms;
var hasRequiredEsObjectAtoms;
function requireEsObjectAtoms() {
  if (hasRequiredEsObjectAtoms) return esObjectAtoms;
  hasRequiredEsObjectAtoms = 1;
  esObjectAtoms = Object;
  return esObjectAtoms;
}
var esErrors;
var hasRequiredEsErrors;
function requireEsErrors() {
  if (hasRequiredEsErrors) return esErrors;
  hasRequiredEsErrors = 1;
  esErrors = Error;
  return esErrors;
}
var _eval;
var hasRequired_eval;
function require_eval() {
  if (hasRequired_eval) return _eval;
  hasRequired_eval = 1;
  _eval = EvalError;
  return _eval;
}
var range;
var hasRequiredRange;
function requireRange() {
  if (hasRequiredRange) return range;
  hasRequiredRange = 1;
  range = RangeError;
  return range;
}
var ref;
var hasRequiredRef;
function requireRef() {
  if (hasRequiredRef) return ref;
  hasRequiredRef = 1;
  ref = ReferenceError;
  return ref;
}
var syntax;
var hasRequiredSyntax;
function requireSyntax() {
  if (hasRequiredSyntax) return syntax;
  hasRequiredSyntax = 1;
  syntax = SyntaxError;
  return syntax;
}
var type;
var hasRequiredType;
function requireType() {
  if (hasRequiredType) return type;
  hasRequiredType = 1;
  type = TypeError;
  return type;
}
var uri;
var hasRequiredUri;
function requireUri() {
  if (hasRequiredUri) return uri;
  hasRequiredUri = 1;
  uri = URIError;
  return uri;
}
var abs;
var hasRequiredAbs;
function requireAbs() {
  if (hasRequiredAbs) return abs;
  hasRequiredAbs = 1;
  abs = Math.abs;
  return abs;
}
var floor;
var hasRequiredFloor;
function requireFloor() {
  if (hasRequiredFloor) return floor;
  hasRequiredFloor = 1;
  floor = Math.floor;
  return floor;
}
var max;
var hasRequiredMax;
function requireMax() {
  if (hasRequiredMax) return max;
  hasRequiredMax = 1;
  max = Math.max;
  return max;
}
var min;
var hasRequiredMin;
function requireMin() {
  if (hasRequiredMin) return min;
  hasRequiredMin = 1;
  min = Math.min;
  return min;
}
var pow;
var hasRequiredPow;
function requirePow() {
  if (hasRequiredPow) return pow;
  hasRequiredPow = 1;
  pow = Math.pow;
  return pow;
}
var round;
var hasRequiredRound;
function requireRound() {
  if (hasRequiredRound) return round;
  hasRequiredRound = 1;
  round = Math.round;
  return round;
}
var _isNaN;
var hasRequired_isNaN;
function require_isNaN() {
  if (hasRequired_isNaN) return _isNaN;
  hasRequired_isNaN = 1;
  _isNaN = Number.isNaN || function isNaN2(a) {
    return a !== a;
  };
  return _isNaN;
}
var sign;
var hasRequiredSign;
function requireSign() {
  if (hasRequiredSign) return sign;
  hasRequiredSign = 1;
  var $isNaN = /* @__PURE__ */ require_isNaN();
  sign = function sign2(number) {
    if ($isNaN(number) || number === 0) {
      return number;
    }
    return number < 0 ? -1 : 1;
  };
  return sign;
}
var gOPD;
var hasRequiredGOPD;
function requireGOPD() {
  if (hasRequiredGOPD) return gOPD;
  hasRequiredGOPD = 1;
  gOPD = Object.getOwnPropertyDescriptor;
  return gOPD;
}
var gopd;
var hasRequiredGopd;
function requireGopd() {
  if (hasRequiredGopd) return gopd;
  hasRequiredGopd = 1;
  var $gOPD = /* @__PURE__ */ requireGOPD();
  if ($gOPD) {
    try {
      $gOPD([], "length");
    } catch (e) {
      $gOPD = null;
    }
  }
  gopd = $gOPD;
  return gopd;
}
var esDefineProperty;
var hasRequiredEsDefineProperty;
function requireEsDefineProperty() {
  if (hasRequiredEsDefineProperty) return esDefineProperty;
  hasRequiredEsDefineProperty = 1;
  var $defineProperty = Object.defineProperty || false;
  if ($defineProperty) {
    try {
      $defineProperty({}, "a", { value: 1 });
    } catch (e) {
      $defineProperty = false;
    }
  }
  esDefineProperty = $defineProperty;
  return esDefineProperty;
}
var shams$1;
var hasRequiredShams$1;
function requireShams$1() {
  if (hasRequiredShams$1) return shams$1;
  hasRequiredShams$1 = 1;
  shams$1 = function hasSymbols2() {
    if (typeof Symbol !== "function" || typeof Object.getOwnPropertySymbols !== "function") {
      return false;
    }
    if (typeof Symbol.iterator === "symbol") {
      return true;
    }
    var obj = {};
    var sym = /* @__PURE__ */ Symbol("test");
    var symObj = Object(sym);
    if (typeof sym === "string") {
      return false;
    }
    if (Object.prototype.toString.call(sym) !== "[object Symbol]") {
      return false;
    }
    if (Object.prototype.toString.call(symObj) !== "[object Symbol]") {
      return false;
    }
    var symVal = 42;
    obj[sym] = symVal;
    for (var _ in obj) {
      return false;
    }
    if (typeof Object.keys === "function" && Object.keys(obj).length !== 0) {
      return false;
    }
    if (typeof Object.getOwnPropertyNames === "function" && Object.getOwnPropertyNames(obj).length !== 0) {
      return false;
    }
    var syms = Object.getOwnPropertySymbols(obj);
    if (syms.length !== 1 || syms[0] !== sym) {
      return false;
    }
    if (!Object.prototype.propertyIsEnumerable.call(obj, sym)) {
      return false;
    }
    if (typeof Object.getOwnPropertyDescriptor === "function") {
      var descriptor = (
        /** @type {PropertyDescriptor} */
        Object.getOwnPropertyDescriptor(obj, sym)
      );
      if (descriptor.value !== symVal || descriptor.enumerable !== true) {
        return false;
      }
    }
    return true;
  };
  return shams$1;
}
var hasSymbols;
var hasRequiredHasSymbols;
function requireHasSymbols() {
  if (hasRequiredHasSymbols) return hasSymbols;
  hasRequiredHasSymbols = 1;
  var origSymbol = typeof Symbol !== "undefined" && Symbol;
  var hasSymbolSham = requireShams$1();
  hasSymbols = function hasNativeSymbols() {
    if (typeof origSymbol !== "function") {
      return false;
    }
    if (typeof Symbol !== "function") {
      return false;
    }
    if (typeof origSymbol("foo") !== "symbol") {
      return false;
    }
    if (typeof /* @__PURE__ */ Symbol("bar") !== "symbol") {
      return false;
    }
    return hasSymbolSham();
  };
  return hasSymbols;
}
var Reflect_getPrototypeOf;
var hasRequiredReflect_getPrototypeOf;
function requireReflect_getPrototypeOf() {
  if (hasRequiredReflect_getPrototypeOf) return Reflect_getPrototypeOf;
  hasRequiredReflect_getPrototypeOf = 1;
  Reflect_getPrototypeOf = typeof Reflect !== "undefined" && Reflect.getPrototypeOf || null;
  return Reflect_getPrototypeOf;
}
var Object_getPrototypeOf;
var hasRequiredObject_getPrototypeOf;
function requireObject_getPrototypeOf() {
  if (hasRequiredObject_getPrototypeOf) return Object_getPrototypeOf;
  hasRequiredObject_getPrototypeOf = 1;
  var $Object = /* @__PURE__ */ requireEsObjectAtoms();
  Object_getPrototypeOf = $Object.getPrototypeOf || null;
  return Object_getPrototypeOf;
}
var implementation;
var hasRequiredImplementation;
function requireImplementation() {
  if (hasRequiredImplementation) return implementation;
  hasRequiredImplementation = 1;
  var ERROR_MESSAGE = "Function.prototype.bind called on incompatible ";
  var toStr = Object.prototype.toString;
  var max2 = Math.max;
  var funcType = "[object Function]";
  var concatty = function concatty2(a, b) {
    var arr = [];
    for (var i = 0; i < a.length; i += 1) {
      arr[i] = a[i];
    }
    for (var j = 0; j < b.length; j += 1) {
      arr[j + a.length] = b[j];
    }
    return arr;
  };
  var slicy = function slicy2(arrLike, offset) {
    var arr = [];
    for (var i = offset, j = 0; i < arrLike.length; i += 1, j += 1) {
      arr[j] = arrLike[i];
    }
    return arr;
  };
  var joiny = function(arr, joiner) {
    var str = "";
    for (var i = 0; i < arr.length; i += 1) {
      str += arr[i];
      if (i + 1 < arr.length) {
        str += joiner;
      }
    }
    return str;
  };
  implementation = function bind2(that) {
    var target = this;
    if (typeof target !== "function" || toStr.apply(target) !== funcType) {
      throw new TypeError(ERROR_MESSAGE + target);
    }
    var args = slicy(arguments, 1);
    var bound;
    var binder = function() {
      if (this instanceof bound) {
        var result = target.apply(
          this,
          concatty(args, arguments)
        );
        if (Object(result) === result) {
          return result;
        }
        return this;
      }
      return target.apply(
        that,
        concatty(args, arguments)
      );
    };
    var boundLength = max2(0, target.length - args.length);
    var boundArgs = [];
    for (var i = 0; i < boundLength; i++) {
      boundArgs[i] = "$" + i;
    }
    bound = Function("binder", "return function (" + joiny(boundArgs, ",") + "){ return binder.apply(this,arguments); }")(binder);
    if (target.prototype) {
      var Empty = function Empty2() {
      };
      Empty.prototype = target.prototype;
      bound.prototype = new Empty();
      Empty.prototype = null;
    }
    return bound;
  };
  return implementation;
}
var functionBind;
var hasRequiredFunctionBind;
function requireFunctionBind() {
  if (hasRequiredFunctionBind) return functionBind;
  hasRequiredFunctionBind = 1;
  var implementation2 = requireImplementation();
  functionBind = Function.prototype.bind || implementation2;
  return functionBind;
}
var functionCall;
var hasRequiredFunctionCall;
function requireFunctionCall() {
  if (hasRequiredFunctionCall) return functionCall;
  hasRequiredFunctionCall = 1;
  functionCall = Function.prototype.call;
  return functionCall;
}
var functionApply;
var hasRequiredFunctionApply;
function requireFunctionApply() {
  if (hasRequiredFunctionApply) return functionApply;
  hasRequiredFunctionApply = 1;
  functionApply = Function.prototype.apply;
  return functionApply;
}
var reflectApply;
var hasRequiredReflectApply;
function requireReflectApply() {
  if (hasRequiredReflectApply) return reflectApply;
  hasRequiredReflectApply = 1;
  reflectApply = typeof Reflect !== "undefined" && Reflect && Reflect.apply;
  return reflectApply;
}
var actualApply;
var hasRequiredActualApply;
function requireActualApply() {
  if (hasRequiredActualApply) return actualApply;
  hasRequiredActualApply = 1;
  var bind2 = requireFunctionBind();
  var $apply = requireFunctionApply();
  var $call = requireFunctionCall();
  var $reflectApply = requireReflectApply();
  actualApply = $reflectApply || bind2.call($call, $apply);
  return actualApply;
}
var callBindApplyHelpers;
var hasRequiredCallBindApplyHelpers;
function requireCallBindApplyHelpers() {
  if (hasRequiredCallBindApplyHelpers) return callBindApplyHelpers;
  hasRequiredCallBindApplyHelpers = 1;
  var bind2 = requireFunctionBind();
  var $TypeError = /* @__PURE__ */ requireType();
  var $call = requireFunctionCall();
  var $actualApply = requireActualApply();
  callBindApplyHelpers = function callBindBasic(args) {
    if (args.length < 1 || typeof args[0] !== "function") {
      throw new $TypeError("a function is required");
    }
    return $actualApply(bind2, $call, args);
  };
  return callBindApplyHelpers;
}
var get;
var hasRequiredGet;
function requireGet() {
  if (hasRequiredGet) return get;
  hasRequiredGet = 1;
  var callBind = requireCallBindApplyHelpers();
  var gOPD2 = /* @__PURE__ */ requireGopd();
  var hasProtoAccessor;
  try {
    hasProtoAccessor = /** @type {{ __proto__?: typeof Array.prototype }} */
    [].__proto__ === Array.prototype;
  } catch (e) {
    if (!e || typeof e !== "object" || !("code" in e) || e.code !== "ERR_PROTO_ACCESS") {
      throw e;
    }
  }
  var desc = !!hasProtoAccessor && gOPD2 && gOPD2(
    Object.prototype,
    /** @type {keyof typeof Object.prototype} */
    "__proto__"
  );
  var $Object = Object;
  var $getPrototypeOf = $Object.getPrototypeOf;
  get = desc && typeof desc.get === "function" ? callBind([desc.get]) : typeof $getPrototypeOf === "function" ? (
    /** @type {import('./get')} */
    function getDunder(value) {
      return $getPrototypeOf(value == null ? value : $Object(value));
    }
  ) : false;
  return get;
}
var getProto;
var hasRequiredGetProto;
function requireGetProto() {
  if (hasRequiredGetProto) return getProto;
  hasRequiredGetProto = 1;
  var reflectGetProto = requireReflect_getPrototypeOf();
  var originalGetProto = requireObject_getPrototypeOf();
  var getDunderProto = /* @__PURE__ */ requireGet();
  getProto = reflectGetProto ? function getProto2(O) {
    return reflectGetProto(O);
  } : originalGetProto ? function getProto2(O) {
    if (!O || typeof O !== "object" && typeof O !== "function") {
      throw new TypeError("getProto: not an object");
    }
    return originalGetProto(O);
  } : getDunderProto ? function getProto2(O) {
    return getDunderProto(O);
  } : null;
  return getProto;
}
var hasown;
var hasRequiredHasown;
function requireHasown() {
  if (hasRequiredHasown) return hasown;
  hasRequiredHasown = 1;
  var call = Function.prototype.call;
  var $hasOwn = Object.prototype.hasOwnProperty;
  var bind2 = requireFunctionBind();
  hasown = bind2.call(call, $hasOwn);
  return hasown;
}
var getIntrinsic;
var hasRequiredGetIntrinsic;
function requireGetIntrinsic() {
  if (hasRequiredGetIntrinsic) return getIntrinsic;
  hasRequiredGetIntrinsic = 1;
  var undefined$1;
  var $Object = /* @__PURE__ */ requireEsObjectAtoms();
  var $Error = /* @__PURE__ */ requireEsErrors();
  var $EvalError = /* @__PURE__ */ require_eval();
  var $RangeError = /* @__PURE__ */ requireRange();
  var $ReferenceError = /* @__PURE__ */ requireRef();
  var $SyntaxError = /* @__PURE__ */ requireSyntax();
  var $TypeError = /* @__PURE__ */ requireType();
  var $URIError = /* @__PURE__ */ requireUri();
  var abs2 = /* @__PURE__ */ requireAbs();
  var floor2 = /* @__PURE__ */ requireFloor();
  var max2 = /* @__PURE__ */ requireMax();
  var min2 = /* @__PURE__ */ requireMin();
  var pow2 = /* @__PURE__ */ requirePow();
  var round2 = /* @__PURE__ */ requireRound();
  var sign2 = /* @__PURE__ */ requireSign();
  var $Function = Function;
  var getEvalledConstructor = function(expressionSyntax) {
    try {
      return $Function('"use strict"; return (' + expressionSyntax + ").constructor;")();
    } catch (e) {
    }
  };
  var $gOPD = /* @__PURE__ */ requireGopd();
  var $defineProperty = /* @__PURE__ */ requireEsDefineProperty();
  var throwTypeError = function() {
    throw new $TypeError();
  };
  var ThrowTypeError = $gOPD ? (function() {
    try {
      arguments.callee;
      return throwTypeError;
    } catch (calleeThrows) {
      try {
        return $gOPD(arguments, "callee").get;
      } catch (gOPDthrows) {
        return throwTypeError;
      }
    }
  })() : throwTypeError;
  var hasSymbols2 = requireHasSymbols()();
  var getProto2 = requireGetProto();
  var $ObjectGPO = requireObject_getPrototypeOf();
  var $ReflectGPO = requireReflect_getPrototypeOf();
  var $apply = requireFunctionApply();
  var $call = requireFunctionCall();
  var needsEval = {};
  var TypedArray = typeof Uint8Array === "undefined" || !getProto2 ? undefined$1 : getProto2(Uint8Array);
  var INTRINSICS = {
    __proto__: null,
    "%AggregateError%": typeof AggregateError === "undefined" ? undefined$1 : AggregateError,
    "%Array%": Array,
    "%ArrayBuffer%": typeof ArrayBuffer === "undefined" ? undefined$1 : ArrayBuffer,
    "%ArrayIteratorPrototype%": hasSymbols2 && getProto2 ? getProto2([][Symbol.iterator]()) : undefined$1,
    "%AsyncFromSyncIteratorPrototype%": undefined$1,
    "%AsyncFunction%": needsEval,
    "%AsyncGenerator%": needsEval,
    "%AsyncGeneratorFunction%": needsEval,
    "%AsyncIteratorPrototype%": needsEval,
    "%Atomics%": typeof Atomics === "undefined" ? undefined$1 : Atomics,
    "%BigInt%": typeof BigInt === "undefined" ? undefined$1 : BigInt,
    "%BigInt64Array%": typeof BigInt64Array === "undefined" ? undefined$1 : BigInt64Array,
    "%BigUint64Array%": typeof BigUint64Array === "undefined" ? undefined$1 : BigUint64Array,
    "%Boolean%": Boolean,
    "%DataView%": typeof DataView === "undefined" ? undefined$1 : DataView,
    "%Date%": Date,
    "%decodeURI%": decodeURI,
    "%decodeURIComponent%": decodeURIComponent,
    "%encodeURI%": encodeURI,
    "%encodeURIComponent%": encodeURIComponent,
    "%Error%": $Error,
    "%eval%": eval,
    // eslint-disable-line no-eval
    "%EvalError%": $EvalError,
    "%Float16Array%": typeof Float16Array === "undefined" ? undefined$1 : Float16Array,
    "%Float32Array%": typeof Float32Array === "undefined" ? undefined$1 : Float32Array,
    "%Float64Array%": typeof Float64Array === "undefined" ? undefined$1 : Float64Array,
    "%FinalizationRegistry%": typeof FinalizationRegistry === "undefined" ? undefined$1 : FinalizationRegistry,
    "%Function%": $Function,
    "%GeneratorFunction%": needsEval,
    "%Int8Array%": typeof Int8Array === "undefined" ? undefined$1 : Int8Array,
    "%Int16Array%": typeof Int16Array === "undefined" ? undefined$1 : Int16Array,
    "%Int32Array%": typeof Int32Array === "undefined" ? undefined$1 : Int32Array,
    "%isFinite%": isFinite,
    "%isNaN%": isNaN,
    "%IteratorPrototype%": hasSymbols2 && getProto2 ? getProto2(getProto2([][Symbol.iterator]())) : undefined$1,
    "%JSON%": typeof JSON === "object" ? JSON : undefined$1,
    "%Map%": typeof Map === "undefined" ? undefined$1 : Map,
    "%MapIteratorPrototype%": typeof Map === "undefined" || !hasSymbols2 || !getProto2 ? undefined$1 : getProto2((/* @__PURE__ */ new Map())[Symbol.iterator]()),
    "%Math%": Math,
    "%Number%": Number,
    "%Object%": $Object,
    "%Object.getOwnPropertyDescriptor%": $gOPD,
    "%parseFloat%": parseFloat,
    "%parseInt%": parseInt,
    "%Promise%": typeof Promise === "undefined" ? undefined$1 : Promise,
    "%Proxy%": typeof Proxy === "undefined" ? undefined$1 : Proxy,
    "%RangeError%": $RangeError,
    "%ReferenceError%": $ReferenceError,
    "%Reflect%": typeof Reflect === "undefined" ? undefined$1 : Reflect,
    "%RegExp%": RegExp,
    "%Set%": typeof Set === "undefined" ? undefined$1 : Set,
    "%SetIteratorPrototype%": typeof Set === "undefined" || !hasSymbols2 || !getProto2 ? undefined$1 : getProto2((/* @__PURE__ */ new Set())[Symbol.iterator]()),
    "%SharedArrayBuffer%": typeof SharedArrayBuffer === "undefined" ? undefined$1 : SharedArrayBuffer,
    "%String%": String,
    "%StringIteratorPrototype%": hasSymbols2 && getProto2 ? getProto2(""[Symbol.iterator]()) : undefined$1,
    "%Symbol%": hasSymbols2 ? Symbol : undefined$1,
    "%SyntaxError%": $SyntaxError,
    "%ThrowTypeError%": ThrowTypeError,
    "%TypedArray%": TypedArray,
    "%TypeError%": $TypeError,
    "%Uint8Array%": typeof Uint8Array === "undefined" ? undefined$1 : Uint8Array,
    "%Uint8ClampedArray%": typeof Uint8ClampedArray === "undefined" ? undefined$1 : Uint8ClampedArray,
    "%Uint16Array%": typeof Uint16Array === "undefined" ? undefined$1 : Uint16Array,
    "%Uint32Array%": typeof Uint32Array === "undefined" ? undefined$1 : Uint32Array,
    "%URIError%": $URIError,
    "%WeakMap%": typeof WeakMap === "undefined" ? undefined$1 : WeakMap,
    "%WeakRef%": typeof WeakRef === "undefined" ? undefined$1 : WeakRef,
    "%WeakSet%": typeof WeakSet === "undefined" ? undefined$1 : WeakSet,
    "%Function.prototype.call%": $call,
    "%Function.prototype.apply%": $apply,
    "%Object.defineProperty%": $defineProperty,
    "%Object.getPrototypeOf%": $ObjectGPO,
    "%Math.abs%": abs2,
    "%Math.floor%": floor2,
    "%Math.max%": max2,
    "%Math.min%": min2,
    "%Math.pow%": pow2,
    "%Math.round%": round2,
    "%Math.sign%": sign2,
    "%Reflect.getPrototypeOf%": $ReflectGPO
  };
  if (getProto2) {
    try {
      null.error;
    } catch (e) {
      var errorProto = getProto2(getProto2(e));
      INTRINSICS["%Error.prototype%"] = errorProto;
    }
  }
  var doEval = function doEval2(name) {
    var value;
    if (name === "%AsyncFunction%") {
      value = getEvalledConstructor("async function () {}");
    } else if (name === "%GeneratorFunction%") {
      value = getEvalledConstructor("function* () {}");
    } else if (name === "%AsyncGeneratorFunction%") {
      value = getEvalledConstructor("async function* () {}");
    } else if (name === "%AsyncGenerator%") {
      var fn = doEval2("%AsyncGeneratorFunction%");
      if (fn) {
        value = fn.prototype;
      }
    } else if (name === "%AsyncIteratorPrototype%") {
      var gen = doEval2("%AsyncGenerator%");
      if (gen && getProto2) {
        value = getProto2(gen.prototype);
      }
    }
    INTRINSICS[name] = value;
    return value;
  };
  var LEGACY_ALIASES = {
    __proto__: null,
    "%ArrayBufferPrototype%": ["ArrayBuffer", "prototype"],
    "%ArrayPrototype%": ["Array", "prototype"],
    "%ArrayProto_entries%": ["Array", "prototype", "entries"],
    "%ArrayProto_forEach%": ["Array", "prototype", "forEach"],
    "%ArrayProto_keys%": ["Array", "prototype", "keys"],
    "%ArrayProto_values%": ["Array", "prototype", "values"],
    "%AsyncFunctionPrototype%": ["AsyncFunction", "prototype"],
    "%AsyncGenerator%": ["AsyncGeneratorFunction", "prototype"],
    "%AsyncGeneratorPrototype%": ["AsyncGeneratorFunction", "prototype", "prototype"],
    "%BooleanPrototype%": ["Boolean", "prototype"],
    "%DataViewPrototype%": ["DataView", "prototype"],
    "%DatePrototype%": ["Date", "prototype"],
    "%ErrorPrototype%": ["Error", "prototype"],
    "%EvalErrorPrototype%": ["EvalError", "prototype"],
    "%Float32ArrayPrototype%": ["Float32Array", "prototype"],
    "%Float64ArrayPrototype%": ["Float64Array", "prototype"],
    "%FunctionPrototype%": ["Function", "prototype"],
    "%Generator%": ["GeneratorFunction", "prototype"],
    "%GeneratorPrototype%": ["GeneratorFunction", "prototype", "prototype"],
    "%Int8ArrayPrototype%": ["Int8Array", "prototype"],
    "%Int16ArrayPrototype%": ["Int16Array", "prototype"],
    "%Int32ArrayPrototype%": ["Int32Array", "prototype"],
    "%JSONParse%": ["JSON", "parse"],
    "%JSONStringify%": ["JSON", "stringify"],
    "%MapPrototype%": ["Map", "prototype"],
    "%NumberPrototype%": ["Number", "prototype"],
    "%ObjectPrototype%": ["Object", "prototype"],
    "%ObjProto_toString%": ["Object", "prototype", "toString"],
    "%ObjProto_valueOf%": ["Object", "prototype", "valueOf"],
    "%PromisePrototype%": ["Promise", "prototype"],
    "%PromiseProto_then%": ["Promise", "prototype", "then"],
    "%Promise_all%": ["Promise", "all"],
    "%Promise_reject%": ["Promise", "reject"],
    "%Promise_resolve%": ["Promise", "resolve"],
    "%RangeErrorPrototype%": ["RangeError", "prototype"],
    "%ReferenceErrorPrototype%": ["ReferenceError", "prototype"],
    "%RegExpPrototype%": ["RegExp", "prototype"],
    "%SetPrototype%": ["Set", "prototype"],
    "%SharedArrayBufferPrototype%": ["SharedArrayBuffer", "prototype"],
    "%StringPrototype%": ["String", "prototype"],
    "%SymbolPrototype%": ["Symbol", "prototype"],
    "%SyntaxErrorPrototype%": ["SyntaxError", "prototype"],
    "%TypedArrayPrototype%": ["TypedArray", "prototype"],
    "%TypeErrorPrototype%": ["TypeError", "prototype"],
    "%Uint8ArrayPrototype%": ["Uint8Array", "prototype"],
    "%Uint8ClampedArrayPrototype%": ["Uint8ClampedArray", "prototype"],
    "%Uint16ArrayPrototype%": ["Uint16Array", "prototype"],
    "%Uint32ArrayPrototype%": ["Uint32Array", "prototype"],
    "%URIErrorPrototype%": ["URIError", "prototype"],
    "%WeakMapPrototype%": ["WeakMap", "prototype"],
    "%WeakSetPrototype%": ["WeakSet", "prototype"]
  };
  var bind2 = requireFunctionBind();
  var hasOwn2 = /* @__PURE__ */ requireHasown();
  var $concat = bind2.call($call, Array.prototype.concat);
  var $spliceApply = bind2.call($apply, Array.prototype.splice);
  var $replace = bind2.call($call, String.prototype.replace);
  var $strSlice = bind2.call($call, String.prototype.slice);
  var $exec = bind2.call($call, RegExp.prototype.exec);
  var rePropName = /[^%.[\]]+|\[(?:(-?\d+(?:\.\d+)?)|(["'])((?:(?!\2)[^\\]|\\.)*?)\2)\]|(?=(?:\.|\[\])(?:\.|\[\]|%$))/g;
  var reEscapeChar = /\\(\\)?/g;
  var stringToPath = function stringToPath2(string) {
    var first = $strSlice(string, 0, 1);
    var last2 = $strSlice(string, -1);
    if (first === "%" && last2 !== "%") {
      throw new $SyntaxError("invalid intrinsic syntax, expected closing `%`");
    } else if (last2 === "%" && first !== "%") {
      throw new $SyntaxError("invalid intrinsic syntax, expected opening `%`");
    }
    var result = [];
    $replace(string, rePropName, function(match, number, quote, subString) {
      result[result.length] = quote ? $replace(subString, reEscapeChar, "$1") : number || match;
    });
    return result;
  };
  var getBaseIntrinsic = function getBaseIntrinsic2(name, allowMissing) {
    var intrinsicName = name;
    var alias;
    if (hasOwn2(LEGACY_ALIASES, intrinsicName)) {
      alias = LEGACY_ALIASES[intrinsicName];
      intrinsicName = "%" + alias[0] + "%";
    }
    if (hasOwn2(INTRINSICS, intrinsicName)) {
      var value = INTRINSICS[intrinsicName];
      if (value === needsEval) {
        value = doEval(intrinsicName);
      }
      if (typeof value === "undefined" && !allowMissing) {
        throw new $TypeError("intrinsic " + name + " exists, but is not available. Please file an issue!");
      }
      return {
        alias,
        name: intrinsicName,
        value
      };
    }
    throw new $SyntaxError("intrinsic " + name + " does not exist!");
  };
  getIntrinsic = function GetIntrinsic(name, allowMissing) {
    if (typeof name !== "string" || name.length === 0) {
      throw new $TypeError("intrinsic name must be a non-empty string");
    }
    if (arguments.length > 1 && typeof allowMissing !== "boolean") {
      throw new $TypeError('"allowMissing" argument must be a boolean');
    }
    if ($exec(/^%?[^%]*%?$/, name) === null) {
      throw new $SyntaxError("`%` may not be present anywhere but at the beginning and end of the intrinsic name");
    }
    var parts = stringToPath(name);
    var intrinsicBaseName = parts.length > 0 ? parts[0] : "";
    var intrinsic = getBaseIntrinsic("%" + intrinsicBaseName + "%", allowMissing);
    var intrinsicRealName = intrinsic.name;
    var value = intrinsic.value;
    var skipFurtherCaching = false;
    var alias = intrinsic.alias;
    if (alias) {
      intrinsicBaseName = alias[0];
      $spliceApply(parts, $concat([0, 1], alias));
    }
    for (var i = 1, isOwn = true; i < parts.length; i += 1) {
      var part = parts[i];
      var first = $strSlice(part, 0, 1);
      var last2 = $strSlice(part, -1);
      if ((first === '"' || first === "'" || first === "`" || (last2 === '"' || last2 === "'" || last2 === "`")) && first !== last2) {
        throw new $SyntaxError("property names with quotes must have matching quotes");
      }
      if (part === "constructor" || !isOwn) {
        skipFurtherCaching = true;
      }
      intrinsicBaseName += "." + part;
      intrinsicRealName = "%" + intrinsicBaseName + "%";
      if (hasOwn2(INTRINSICS, intrinsicRealName)) {
        value = INTRINSICS[intrinsicRealName];
      } else if (value != null) {
        if (!(part in value)) {
          if (!allowMissing) {
            throw new $TypeError("base intrinsic for " + name + " exists, but the property is not available.");
          }
          return void undefined$1;
        }
        if ($gOPD && i + 1 >= parts.length) {
          var desc = $gOPD(value, part);
          isOwn = !!desc;
          if (isOwn && "get" in desc && !("originalValue" in desc.get)) {
            value = desc.get;
          } else {
            value = value[part];
          }
        } else {
          isOwn = hasOwn2(value, part);
          value = value[part];
        }
        if (isOwn && !skipFurtherCaching) {
          INTRINSICS[intrinsicRealName] = value;
        }
      }
    }
    return value;
  };
  return getIntrinsic;
}
var shams;
var hasRequiredShams;
function requireShams() {
  if (hasRequiredShams) return shams;
  hasRequiredShams = 1;
  var hasSymbols2 = requireShams$1();
  shams = function hasToStringTagShams() {
    return hasSymbols2() && !!Symbol.toStringTag;
  };
  return shams;
}
var esSetTostringtag;
var hasRequiredEsSetTostringtag;
function requireEsSetTostringtag() {
  if (hasRequiredEsSetTostringtag) return esSetTostringtag;
  hasRequiredEsSetTostringtag = 1;
  var GetIntrinsic = /* @__PURE__ */ requireGetIntrinsic();
  var $defineProperty = GetIntrinsic("%Object.defineProperty%", true);
  var hasToStringTag = requireShams()();
  var hasOwn2 = /* @__PURE__ */ requireHasown();
  var $TypeError = /* @__PURE__ */ requireType();
  var toStringTag2 = hasToStringTag ? Symbol.toStringTag : null;
  esSetTostringtag = function setToStringTag(object, value) {
    var overrideIfSet = arguments.length > 2 && !!arguments[2] && arguments[2].force;
    var nonConfigurable = arguments.length > 2 && !!arguments[2] && arguments[2].nonConfigurable;
    if (typeof overrideIfSet !== "undefined" && typeof overrideIfSet !== "boolean" || typeof nonConfigurable !== "undefined" && typeof nonConfigurable !== "boolean") {
      throw new $TypeError("if provided, the `overrideIfSet` and `nonConfigurable` options must be booleans");
    }
    if (toStringTag2 && (overrideIfSet || !hasOwn2(object, toStringTag2))) {
      if ($defineProperty) {
        $defineProperty(object, toStringTag2, {
          configurable: !nonConfigurable,
          enumerable: false,
          value,
          writable: false
        });
      } else {
        object[toStringTag2] = value;
      }
    }
  };
  return esSetTostringtag;
}
var populate;
var hasRequiredPopulate;
function requirePopulate() {
  if (hasRequiredPopulate) return populate;
  hasRequiredPopulate = 1;
  populate = function(dst, src2) {
    Object.keys(src2).forEach(function(prop) {
      dst[prop] = dst[prop] || src2[prop];
    });
    return dst;
  };
  return populate;
}
var form_data;
var hasRequiredForm_data;
function requireForm_data() {
  if (hasRequiredForm_data) return form_data;
  hasRequiredForm_data = 1;
  var CombinedStream = requireCombined_stream();
  var util = require$$1;
  var path = require$$1$1;
  var http = require$$3;
  var https = require$$4;
  var parseUrl2 = require$$5.parse;
  var fs = require$$6;
  var Stream = stream.Stream;
  var crypto = require$$8;
  var mime = requireMimeTypes();
  var asynckit2 = requireAsynckit();
  var setToStringTag = /* @__PURE__ */ requireEsSetTostringtag();
  var hasOwn2 = /* @__PURE__ */ requireHasown();
  var populate2 = requirePopulate();
  function FormData2(options) {
    if (!(this instanceof FormData2)) {
      return new FormData2(options);
    }
    this._overheadLength = 0;
    this._valueLength = 0;
    this._valuesToMeasure = [];
    CombinedStream.call(this);
    options = options || {};
    for (var option in options) {
      this[option] = options[option];
    }
  }
  util.inherits(FormData2, CombinedStream);
  FormData2.LINE_BREAK = "\r\n";
  FormData2.DEFAULT_CONTENT_TYPE = "application/octet-stream";
  FormData2.prototype.append = function(field, value, options) {
    options = options || {};
    if (typeof options === "string") {
      options = { filename: options };
    }
    var append2 = CombinedStream.prototype.append.bind(this);
    if (typeof value === "number" || value == null) {
      value = String(value);
    }
    if (Array.isArray(value)) {
      this._error(new Error("Arrays are not supported."));
      return;
    }
    var header = this._multiPartHeader(field, value, options);
    var footer = this._multiPartFooter();
    append2(header);
    append2(value);
    append2(footer);
    this._trackLength(header, value, options);
  };
  FormData2.prototype._trackLength = function(header, value, options) {
    var valueLength = 0;
    if (options.knownLength != null) {
      valueLength += Number(options.knownLength);
    } else if (Buffer.isBuffer(value)) {
      valueLength = value.length;
    } else if (typeof value === "string") {
      valueLength = Buffer.byteLength(value);
    }
    this._valueLength += valueLength;
    this._overheadLength += Buffer.byteLength(header) + FormData2.LINE_BREAK.length;
    if (!value || !value.path && !(value.readable && hasOwn2(value, "httpVersion")) && !(value instanceof Stream)) {
      return;
    }
    if (!options.knownLength) {
      this._valuesToMeasure.push(value);
    }
  };
  FormData2.prototype._lengthRetriever = function(value, callback) {
    if (hasOwn2(value, "fd")) {
      if (value.end != void 0 && value.end != Infinity && value.start != void 0) {
        callback(null, value.end + 1 - (value.start ? value.start : 0));
      } else {
        fs.stat(value.path, function(err, stat) {
          if (err) {
            callback(err);
            return;
          }
          var fileSize = stat.size - (value.start ? value.start : 0);
          callback(null, fileSize);
        });
      }
    } else if (hasOwn2(value, "httpVersion")) {
      callback(null, Number(value.headers["content-length"]));
    } else if (hasOwn2(value, "httpModule")) {
      value.on("response", function(response) {
        value.pause();
        callback(null, Number(response.headers["content-length"]));
      });
      value.resume();
    } else {
      callback("Unknown stream");
    }
  };
  FormData2.prototype._multiPartHeader = function(field, value, options) {
    if (typeof options.header === "string") {
      return options.header;
    }
    var contentDisposition = this._getContentDisposition(value, options);
    var contentType = this._getContentType(value, options);
    var contents = "";
    var headers = {
      // add custom disposition as third element or keep it two elements if not
      "Content-Disposition": ["form-data", 'name="' + field + '"'].concat(contentDisposition || []),
      // if no content type. allow it to be empty array
      "Content-Type": [].concat(contentType || [])
    };
    if (typeof options.header === "object") {
      populate2(headers, options.header);
    }
    var header;
    for (var prop in headers) {
      if (hasOwn2(headers, prop)) {
        header = headers[prop];
        if (header == null) {
          continue;
        }
        if (!Array.isArray(header)) {
          header = [header];
        }
        if (header.length) {
          contents += prop + ": " + header.join("; ") + FormData2.LINE_BREAK;
        }
      }
    }
    return "--" + this.getBoundary() + FormData2.LINE_BREAK + contents + FormData2.LINE_BREAK;
  };
  FormData2.prototype._getContentDisposition = function(value, options) {
    var filename;
    if (typeof options.filepath === "string") {
      filename = path.normalize(options.filepath).replace(/\\/g, "/");
    } else if (options.filename || value && (value.name || value.path)) {
      filename = path.basename(options.filename || value && (value.name || value.path));
    } else if (value && value.readable && hasOwn2(value, "httpVersion")) {
      filename = path.basename(value.client._httpMessage.path || "");
    }
    if (filename) {
      return 'filename="' + filename + '"';
    }
  };
  FormData2.prototype._getContentType = function(value, options) {
    var contentType = options.contentType;
    if (!contentType && value && value.name) {
      contentType = mime.lookup(value.name);
    }
    if (!contentType && value && value.path) {
      contentType = mime.lookup(value.path);
    }
    if (!contentType && value && value.readable && hasOwn2(value, "httpVersion")) {
      contentType = value.headers["content-type"];
    }
    if (!contentType && (options.filepath || options.filename)) {
      contentType = mime.lookup(options.filepath || options.filename);
    }
    if (!contentType && value && typeof value === "object") {
      contentType = FormData2.DEFAULT_CONTENT_TYPE;
    }
    return contentType;
  };
  FormData2.prototype._multiPartFooter = function() {
    return function(next) {
      var footer = FormData2.LINE_BREAK;
      var lastPart = this._streams.length === 0;
      if (lastPart) {
        footer += this._lastBoundary();
      }
      next(footer);
    }.bind(this);
  };
  FormData2.prototype._lastBoundary = function() {
    return "--" + this.getBoundary() + "--" + FormData2.LINE_BREAK;
  };
  FormData2.prototype.getHeaders = function(userHeaders) {
    var header;
    var formHeaders = {
      "content-type": "multipart/form-data; boundary=" + this.getBoundary()
    };
    for (header in userHeaders) {
      if (hasOwn2(userHeaders, header)) {
        formHeaders[header.toLowerCase()] = userHeaders[header];
      }
    }
    return formHeaders;
  };
  FormData2.prototype.setBoundary = function(boundary) {
    if (typeof boundary !== "string") {
      throw new TypeError("FormData boundary must be a string");
    }
    this._boundary = boundary;
  };
  FormData2.prototype.getBoundary = function() {
    if (!this._boundary) {
      this._generateBoundary();
    }
    return this._boundary;
  };
  FormData2.prototype.getBuffer = function() {
    var dataBuffer = new Buffer.alloc(0);
    var boundary = this.getBoundary();
    for (var i = 0, len = this._streams.length; i < len; i++) {
      if (typeof this._streams[i] !== "function") {
        if (Buffer.isBuffer(this._streams[i])) {
          dataBuffer = Buffer.concat([dataBuffer, this._streams[i]]);
        } else {
          dataBuffer = Buffer.concat([dataBuffer, Buffer.from(this._streams[i])]);
        }
        if (typeof this._streams[i] !== "string" || this._streams[i].substring(2, boundary.length + 2) !== boundary) {
          dataBuffer = Buffer.concat([dataBuffer, Buffer.from(FormData2.LINE_BREAK)]);
        }
      }
    }
    return Buffer.concat([dataBuffer, Buffer.from(this._lastBoundary())]);
  };
  FormData2.prototype._generateBoundary = function() {
    this._boundary = "--------------------------" + crypto.randomBytes(12).toString("hex");
  };
  FormData2.prototype.getLengthSync = function() {
    var knownLength = this._overheadLength + this._valueLength;
    if (this._streams.length) {
      knownLength += this._lastBoundary().length;
    }
    if (!this.hasKnownLength()) {
      this._error(new Error("Cannot calculate proper length in synchronous way."));
    }
    return knownLength;
  };
  FormData2.prototype.hasKnownLength = function() {
    var hasKnownLength = true;
    if (this._valuesToMeasure.length) {
      hasKnownLength = false;
    }
    return hasKnownLength;
  };
  FormData2.prototype.getLength = function(cb) {
    var knownLength = this._overheadLength + this._valueLength;
    if (this._streams.length) {
      knownLength += this._lastBoundary().length;
    }
    if (!this._valuesToMeasure.length) {
      process.nextTick(cb.bind(this, null, knownLength));
      return;
    }
    asynckit2.parallel(this._valuesToMeasure, this._lengthRetriever, function(err, values) {
      if (err) {
        cb(err);
        return;
      }
      values.forEach(function(length) {
        knownLength += length;
      });
      cb(null, knownLength);
    });
  };
  FormData2.prototype.submit = function(params, cb) {
    var request;
    var options;
    var defaults2 = { method: "post" };
    if (typeof params === "string") {
      params = parseUrl2(params);
      options = populate2({
        port: params.port,
        path: params.pathname,
        host: params.hostname,
        protocol: params.protocol
      }, defaults2);
    } else {
      options = populate2(params, defaults2);
      if (!options.port) {
        options.port = options.protocol === "https:" ? 443 : 80;
      }
    }
    options.headers = this.getHeaders(params.headers);
    if (options.protocol === "https:") {
      request = https.request(options);
    } else {
      request = http.request(options);
    }
    this.getLength(function(err, length) {
      if (err && err !== "Unknown stream") {
        this._error(err);
        return;
      }
      if (length) {
        request.setHeader("Content-Length", length);
      }
      this.pipe(request);
      if (cb) {
        var onResponse;
        var callback = function(error, responce) {
          request.removeListener("error", callback);
          request.removeListener("response", onResponse);
          return cb.call(this, error, responce);
        };
        onResponse = callback.bind(this, null);
        request.on("error", callback);
        request.on("response", onResponse);
      }
    }.bind(this));
    return request;
  };
  FormData2.prototype._error = function(err) {
    if (!this.error) {
      this.error = err;
      this.pause();
      this.emit("error", err);
    }
  };
  FormData2.prototype.toString = function() {
    return "[object FormData]";
  };
  setToStringTag(FormData2.prototype, "FormData");
  form_data = FormData2;
  return form_data;
}
var form_dataExports = requireForm_data();
const FormData$1 = /* @__PURE__ */ getDefaultExportFromCjs(form_dataExports);
function isVisitable(thing) {
  return utils$1.isPlainObject(thing) || utils$1.isArray(thing);
}
function removeBrackets(key) {
  return utils$1.endsWith(key, "[]") ? key.slice(0, -2) : key;
}
function renderKey(path, key, dots) {
  if (!path) return key;
  return path.concat(key).map(function each(token, i) {
    token = removeBrackets(token);
    return !dots && i ? "[" + token + "]" : token;
  }).join(dots ? "." : "");
}
function isFlatArray(arr) {
  return utils$1.isArray(arr) && !arr.some(isVisitable);
}
const predicates = utils$1.toFlatObject(utils$1, {}, null, function filter(prop) {
  return /^is[A-Z]/.test(prop);
});
function toFormData$1(obj, formData, options) {
  if (!utils$1.isObject(obj)) {
    throw new TypeError("target must be an object");
  }
  formData = formData || new (FormData$1 || FormData)();
  options = utils$1.toFlatObject(
    options,
    {
      metaTokens: true,
      dots: false,
      indexes: false
    },
    false,
    function defined(option, source) {
      return !utils$1.isUndefined(source[option]);
    }
  );
  const metaTokens = options.metaTokens;
  const visitor = options.visitor || defaultVisitor;
  const dots = options.dots;
  const indexes = options.indexes;
  const _Blob = options.Blob || typeof Blob !== "undefined" && Blob;
  const maxDepth = options.maxDepth === void 0 ? 100 : options.maxDepth;
  const useBlob = _Blob && utils$1.isSpecCompliantForm(formData);
  if (!utils$1.isFunction(visitor)) {
    throw new TypeError("visitor must be a function");
  }
  function convertValue(value) {
    if (value === null) return "";
    if (utils$1.isDate(value)) {
      return value.toISOString();
    }
    if (utils$1.isBoolean(value)) {
      return value.toString();
    }
    if (!useBlob && utils$1.isBlob(value)) {
      throw new AxiosError$1("Blob is not supported. Use a Buffer instead.");
    }
    if (utils$1.isArrayBuffer(value) || utils$1.isTypedArray(value)) {
      return useBlob && typeof Blob === "function" ? new Blob([value]) : Buffer.from(value);
    }
    return value;
  }
  function defaultVisitor(value, key, path) {
    let arr = value;
    if (utils$1.isReactNative(formData) && utils$1.isReactNativeBlob(value)) {
      formData.append(renderKey(path, key, dots), convertValue(value));
      return false;
    }
    if (value && !path && typeof value === "object") {
      if (utils$1.endsWith(key, "{}")) {
        key = metaTokens ? key : key.slice(0, -2);
        value = JSON.stringify(value);
      } else if (utils$1.isArray(value) && isFlatArray(value) || (utils$1.isFileList(value) || utils$1.endsWith(key, "[]")) && (arr = utils$1.toArray(value))) {
        key = removeBrackets(key);
        arr.forEach(function each(el, index) {
          !(utils$1.isUndefined(el) || el === null) && formData.append(
            // eslint-disable-next-line no-nested-ternary
            indexes === true ? renderKey([key], index, dots) : indexes === null ? key : key + "[]",
            convertValue(el)
          );
        });
        return false;
      }
    }
    if (isVisitable(value)) {
      return true;
    }
    formData.append(renderKey(path, key, dots), convertValue(value));
    return false;
  }
  const stack = [];
  const exposedHelpers = Object.assign(predicates, {
    defaultVisitor,
    convertValue,
    isVisitable
  });
  function build(value, path, depth = 0) {
    if (utils$1.isUndefined(value)) return;
    if (depth > maxDepth) {
      throw new AxiosError$1(
        "Object is too deeply nested (" + depth + " levels). Max depth: " + maxDepth,
        AxiosError$1.ERR_FORM_DATA_DEPTH_EXCEEDED
      );
    }
    if (stack.indexOf(value) !== -1) {
      throw Error("Circular reference detected in " + path.join("."));
    }
    stack.push(value);
    utils$1.forEach(value, function each(el, key) {
      const result = !(utils$1.isUndefined(el) || el === null) && visitor.call(formData, el, utils$1.isString(key) ? key.trim() : key, path, exposedHelpers);
      if (result === true) {
        build(el, path ? path.concat(key) : [key], depth + 1);
      }
    });
    stack.pop();
  }
  if (!utils$1.isObject(obj)) {
    throw new TypeError("data must be an object");
  }
  build(obj);
  return formData;
}
function encode$1(str) {
  const charMap = {
    "!": "%21",
    "'": "%27",
    "(": "%28",
    ")": "%29",
    "~": "%7E",
    "%20": "+"
  };
  return encodeURIComponent(str).replace(/[!'()~]|%20/g, function replacer(match) {
    return charMap[match];
  });
}
function AxiosURLSearchParams(params, options) {
  this._pairs = [];
  params && toFormData$1(params, this, options);
}
const prototype = AxiosURLSearchParams.prototype;
prototype.append = function append(name, value) {
  this._pairs.push([name, value]);
};
prototype.toString = function toString2(encoder) {
  const _encode = encoder ? function(value) {
    return encoder.call(this, value, encode$1);
  } : encode$1;
  return this._pairs.map(function each(pair) {
    return _encode(pair[0]) + "=" + _encode(pair[1]);
  }, "").join("&");
};
function encode(val) {
  return encodeURIComponent(val).replace(/%3A/gi, ":").replace(/%24/g, "$").replace(/%2C/gi, ",").replace(/%20/g, "+");
}
function buildURL(url, params, options) {
  if (!params) {
    return url;
  }
  const _encode = options && options.encode || encode;
  const _options = utils$1.isFunction(options) ? {
    serialize: options
  } : options;
  const serializeFn = _options && _options.serialize;
  let serializedParams;
  if (serializeFn) {
    serializedParams = serializeFn(params, _options);
  } else {
    serializedParams = utils$1.isURLSearchParams(params) ? params.toString() : new AxiosURLSearchParams(params, _options).toString(_encode);
  }
  if (serializedParams) {
    const hashmarkIndex = url.indexOf("#");
    if (hashmarkIndex !== -1) {
      url = url.slice(0, hashmarkIndex);
    }
    url += (url.indexOf("?") === -1 ? "?" : "&") + serializedParams;
  }
  return url;
}
class InterceptorManager {
  constructor() {
    this.handlers = [];
  }
  /**
   * Add a new interceptor to the stack
   *
   * @param {Function} fulfilled The function to handle `then` for a `Promise`
   * @param {Function} rejected The function to handle `reject` for a `Promise`
   * @param {Object} options The options for the interceptor, synchronous and runWhen
   *
   * @return {Number} An ID used to remove interceptor later
   */
  use(fulfilled, rejected, options) {
    this.handlers.push({
      fulfilled,
      rejected,
      synchronous: options ? options.synchronous : false,
      runWhen: options ? options.runWhen : null
    });
    return this.handlers.length - 1;
  }
  /**
   * Remove an interceptor from the stack
   *
   * @param {Number} id The ID that was returned by `use`
   *
   * @returns {void}
   */
  eject(id) {
    if (this.handlers[id]) {
      this.handlers[id] = null;
    }
  }
  /**
   * Clear all interceptors from the stack
   *
   * @returns {void}
   */
  clear() {
    if (this.handlers) {
      this.handlers = [];
    }
  }
  /**
   * Iterate over all the registered interceptors
   *
   * This method is particularly useful for skipping over any
   * interceptors that may have become `null` calling `eject`.
   *
   * @param {Function} fn The function to call for each interceptor
   *
   * @returns {void}
   */
  forEach(fn) {
    utils$1.forEach(this.handlers, function forEachHandler(h) {
      if (h !== null) {
        fn(h);
      }
    });
  }
}
const transitionalDefaults = {
  silentJSONParsing: true,
  forcedJSONParsing: true,
  clarifyTimeoutError: false,
  legacyInterceptorReqResOrdering: true
};
const URLSearchParams$1 = require$$5.URLSearchParams;
const ALPHA = "abcdefghijklmnopqrstuvwxyz";
const DIGIT = "0123456789";
const ALPHABET = {
  DIGIT,
  ALPHA,
  ALPHA_DIGIT: ALPHA + ALPHA.toUpperCase() + DIGIT
};
const generateString = (size = 16, alphabet = ALPHABET.ALPHA_DIGIT) => {
  let str = "";
  const { length } = alphabet;
  const randomValues = new Uint32Array(size);
  require$$8.randomFillSync(randomValues);
  for (let i = 0; i < size; i++) {
    str += alphabet[randomValues[i] % length];
  }
  return str;
};
const platform$1 = {
  isNode: true,
  classes: {
    URLSearchParams: URLSearchParams$1,
    FormData: FormData$1,
    Blob: typeof Blob !== "undefined" && Blob || null
  },
  ALPHABET,
  generateString,
  protocols: ["http", "https", "file", "data"]
};
const hasBrowserEnv = typeof window !== "undefined" && typeof document !== "undefined";
const _navigator = typeof navigator === "object" && navigator || void 0;
const hasStandardBrowserEnv = hasBrowserEnv && (!_navigator || ["ReactNative", "NativeScript", "NS"].indexOf(_navigator.product) < 0);
const hasStandardBrowserWebWorkerEnv = (() => {
  return typeof WorkerGlobalScope !== "undefined" && // eslint-disable-next-line no-undef
  self instanceof WorkerGlobalScope && typeof self.importScripts === "function";
})();
const origin = hasBrowserEnv && window.location.href || "http://localhost";
const utils = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  hasBrowserEnv,
  hasStandardBrowserEnv,
  hasStandardBrowserWebWorkerEnv,
  navigator: _navigator,
  origin
}, Symbol.toStringTag, { value: "Module" }));
const platform = {
  ...utils,
  ...platform$1
};
function toURLEncodedForm(data, options) {
  return toFormData$1(data, new platform.classes.URLSearchParams(), {
    visitor: function(value, key, path, helpers) {
      if (platform.isNode && utils$1.isBuffer(value)) {
        this.append(key, value.toString("base64"));
        return false;
      }
      return helpers.defaultVisitor.apply(this, arguments);
    },
    ...options
  });
}
function parsePropPath(name) {
  return utils$1.matchAll(/\w+|\[(\w*)]/g, name).map((match) => {
    return match[0] === "[]" ? "" : match[1] || match[0];
  });
}
function arrayToObject(arr) {
  const obj = {};
  const keys = Object.keys(arr);
  let i;
  const len = keys.length;
  let key;
  for (i = 0; i < len; i++) {
    key = keys[i];
    obj[key] = arr[key];
  }
  return obj;
}
function formDataToJSON(formData) {
  function buildPath(path, value, target, index) {
    let name = path[index++];
    if (name === "__proto__") return true;
    const isNumericKey = Number.isFinite(+name);
    const isLast = index >= path.length;
    name = !name && utils$1.isArray(target) ? target.length : name;
    if (isLast) {
      if (utils$1.hasOwnProp(target, name)) {
        target[name] = utils$1.isArray(target[name]) ? target[name].concat(value) : [target[name], value];
      } else {
        target[name] = value;
      }
      return !isNumericKey;
    }
    if (!target[name] || !utils$1.isObject(target[name])) {
      target[name] = [];
    }
    const result = buildPath(path, value, target[name], index);
    if (result && utils$1.isArray(target[name])) {
      target[name] = arrayToObject(target[name]);
    }
    return !isNumericKey;
  }
  if (utils$1.isFormData(formData) && utils$1.isFunction(formData.entries)) {
    const obj = {};
    utils$1.forEachEntry(formData, (name, value) => {
      buildPath(parsePropPath(name), value, obj, 0);
    });
    return obj;
  }
  return null;
}
const own = (obj, key) => obj != null && utils$1.hasOwnProp(obj, key) ? obj[key] : void 0;
function stringifySafely(rawValue, parser, encoder) {
  if (utils$1.isString(rawValue)) {
    try {
      (parser || JSON.parse)(rawValue);
      return utils$1.trim(rawValue);
    } catch (e) {
      if (e.name !== "SyntaxError") {
        throw e;
      }
    }
  }
  return (encoder || JSON.stringify)(rawValue);
}
const defaults = {
  transitional: transitionalDefaults,
  adapter: ["xhr", "http", "fetch"],
  transformRequest: [
    function transformRequest(data, headers) {
      const contentType = headers.getContentType() || "";
      const hasJSONContentType = contentType.indexOf("application/json") > -1;
      const isObjectPayload = utils$1.isObject(data);
      if (isObjectPayload && utils$1.isHTMLForm(data)) {
        data = new FormData(data);
      }
      const isFormData2 = utils$1.isFormData(data);
      if (isFormData2) {
        return hasJSONContentType ? JSON.stringify(formDataToJSON(data)) : data;
      }
      if (utils$1.isArrayBuffer(data) || utils$1.isBuffer(data) || utils$1.isStream(data) || utils$1.isFile(data) || utils$1.isBlob(data) || utils$1.isReadableStream(data)) {
        return data;
      }
      if (utils$1.isArrayBufferView(data)) {
        return data.buffer;
      }
      if (utils$1.isURLSearchParams(data)) {
        headers.setContentType("application/x-www-form-urlencoded;charset=utf-8", false);
        return data.toString();
      }
      let isFileList2;
      if (isObjectPayload) {
        const formSerializer = own(this, "formSerializer");
        if (contentType.indexOf("application/x-www-form-urlencoded") > -1) {
          return toURLEncodedForm(data, formSerializer).toString();
        }
        if ((isFileList2 = utils$1.isFileList(data)) || contentType.indexOf("multipart/form-data") > -1) {
          const env = own(this, "env");
          const _FormData = env && env.FormData;
          return toFormData$1(
            isFileList2 ? { "files[]": data } : data,
            _FormData && new _FormData(),
            formSerializer
          );
        }
      }
      if (isObjectPayload || hasJSONContentType) {
        headers.setContentType("application/json", false);
        return stringifySafely(data);
      }
      return data;
    }
  ],
  transformResponse: [
    function transformResponse(data) {
      const transitional2 = own(this, "transitional") || defaults.transitional;
      const forcedJSONParsing = transitional2 && transitional2.forcedJSONParsing;
      const responseType = own(this, "responseType");
      const JSONRequested = responseType === "json";
      if (utils$1.isResponse(data) || utils$1.isReadableStream(data)) {
        return data;
      }
      if (data && utils$1.isString(data) && (forcedJSONParsing && !responseType || JSONRequested)) {
        const silentJSONParsing = transitional2 && transitional2.silentJSONParsing;
        const strictJSONParsing = !silentJSONParsing && JSONRequested;
        try {
          return JSON.parse(data, own(this, "parseReviver"));
        } catch (e) {
          if (strictJSONParsing) {
            if (e.name === "SyntaxError") {
              throw AxiosError$1.from(e, AxiosError$1.ERR_BAD_RESPONSE, this, null, own(this, "response"));
            }
            throw e;
          }
        }
      }
      return data;
    }
  ],
  /**
   * A timeout in milliseconds to abort a request. If set to 0 (default) a
   * timeout is not created.
   */
  timeout: 0,
  xsrfCookieName: "XSRF-TOKEN",
  xsrfHeaderName: "X-XSRF-TOKEN",
  maxContentLength: -1,
  maxBodyLength: -1,
  env: {
    FormData: platform.classes.FormData,
    Blob: platform.classes.Blob
  },
  validateStatus: function validateStatus(status) {
    return status >= 200 && status < 300;
  },
  headers: {
    common: {
      Accept: "application/json, text/plain, */*",
      "Content-Type": void 0
    }
  }
};
utils$1.forEach(["delete", "get", "head", "post", "put", "patch", "query"], (method) => {
  defaults.headers[method] = {};
});
function transformData(fns, response) {
  const config = this || defaults;
  const context = response || config;
  const headers = AxiosHeaders$1.from(context.headers);
  let data = context.data;
  utils$1.forEach(fns, function transform(fn) {
    data = fn.call(config, data, headers.normalize(), response ? response.status : void 0);
  });
  headers.normalize();
  return data;
}
function isCancel$1(value) {
  return !!(value && value.__CANCEL__);
}
let CanceledError$1 = class CanceledError extends AxiosError$1 {
  /**
   * A `CanceledError` is an object that is thrown when an operation is canceled.
   *
   * @param {string=} message The message.
   * @param {Object=} config The config.
   * @param {Object=} request The request.
   *
   * @returns {CanceledError} The created error.
   */
  constructor(message, config, request) {
    super(message == null ? "canceled" : message, AxiosError$1.ERR_CANCELED, config, request);
    this.name = "CanceledError";
    this.__CANCEL__ = true;
  }
};
function settle(resolve2, reject, response) {
  const validateStatus2 = response.config.validateStatus;
  if (!response.status || !validateStatus2 || validateStatus2(response.status)) {
    resolve2(response);
  } else {
    reject(new AxiosError$1(
      "Request failed with status code " + response.status,
      response.status >= 400 && response.status < 500 ? AxiosError$1.ERR_BAD_REQUEST : AxiosError$1.ERR_BAD_RESPONSE,
      response.config,
      response.request,
      response
    ));
  }
}
function isAbsoluteURL(url) {
  if (typeof url !== "string") {
    return false;
  }
  return /^([a-z][a-z\d+\-.]*:)?\/\//i.test(url);
}
function combineURLs(baseURL, relativeURL) {
  return relativeURL ? baseURL.replace(/\/?\/$/, "") + "/" + relativeURL.replace(/^\/+/, "") : baseURL;
}
function buildFullPath(baseURL, requestedURL, allowAbsoluteUrls) {
  let isRelativeUrl = !isAbsoluteURL(requestedURL);
  if (baseURL && (isRelativeUrl || allowAbsoluteUrls === false)) {
    return combineURLs(baseURL, requestedURL);
  }
  return requestedURL;
}
var DEFAULT_PORTS$1 = {
  ftp: 21,
  gopher: 70,
  http: 80,
  https: 443,
  ws: 80,
  wss: 443
};
function parseUrl(urlString) {
  try {
    return new URL(urlString);
  } catch {
    return null;
  }
}
function getProxyForUrl(url) {
  var parsedUrl = (typeof url === "string" ? parseUrl(url) : url) || {};
  var proto = parsedUrl.protocol;
  var hostname = parsedUrl.host;
  var port = parsedUrl.port;
  if (typeof hostname !== "string" || !hostname || typeof proto !== "string") {
    return "";
  }
  proto = proto.split(":", 1)[0];
  hostname = hostname.replace(/:\d*$/, "");
  port = parseInt(port) || DEFAULT_PORTS$1[proto] || 0;
  if (!shouldProxy(hostname, port)) {
    return "";
  }
  var proxy = getEnv(proto + "_proxy") || getEnv("all_proxy");
  if (proxy && proxy.indexOf("://") === -1) {
    proxy = proto + "://" + proxy;
  }
  return proxy;
}
function shouldProxy(hostname, port) {
  var NO_PROXY = getEnv("no_proxy").toLowerCase();
  if (!NO_PROXY) {
    return true;
  }
  if (NO_PROXY === "*") {
    return false;
  }
  return NO_PROXY.split(/[,\s]/).every(function(proxy) {
    if (!proxy) {
      return true;
    }
    var parsedProxy = proxy.match(/^(.+):(\d+)$/);
    var parsedProxyHostname = parsedProxy ? parsedProxy[1] : proxy;
    var parsedProxyPort = parsedProxy ? parseInt(parsedProxy[2]) : 0;
    if (parsedProxyPort && parsedProxyPort !== port) {
      return true;
    }
    if (!/^[.*]/.test(parsedProxyHostname)) {
      return hostname !== parsedProxyHostname;
    }
    if (parsedProxyHostname.charAt(0) === "*") {
      parsedProxyHostname = parsedProxyHostname.slice(1);
    }
    return !hostname.endsWith(parsedProxyHostname);
  });
}
function getEnv(key) {
  return process.env[key.toLowerCase()] || process.env[key.toUpperCase()] || "";
}
var followRedirects$1 = { exports: {} };
var src = { exports: {} };
var browser = { exports: {} };
var ms;
var hasRequiredMs;
function requireMs() {
  if (hasRequiredMs) return ms;
  hasRequiredMs = 1;
  var s = 1e3;
  var m = s * 60;
  var h = m * 60;
  var d = h * 24;
  var w = d * 7;
  var y = d * 365.25;
  ms = function(val, options) {
    options = options || {};
    var type2 = typeof val;
    if (type2 === "string" && val.length > 0) {
      return parse(val);
    } else if (type2 === "number" && isFinite(val)) {
      return options.long ? fmtLong(val) : fmtShort(val);
    }
    throw new Error(
      "val is not a non-empty string or a valid number. val=" + JSON.stringify(val)
    );
  };
  function parse(str) {
    str = String(str);
    if (str.length > 100) {
      return;
    }
    var match = /^(-?(?:\d+)?\.?\d+) *(milliseconds?|msecs?|ms|seconds?|secs?|s|minutes?|mins?|m|hours?|hrs?|h|days?|d|weeks?|w|years?|yrs?|y)?$/i.exec(
      str
    );
    if (!match) {
      return;
    }
    var n = parseFloat(match[1]);
    var type2 = (match[2] || "ms").toLowerCase();
    switch (type2) {
      case "years":
      case "year":
      case "yrs":
      case "yr":
      case "y":
        return n * y;
      case "weeks":
      case "week":
      case "w":
        return n * w;
      case "days":
      case "day":
      case "d":
        return n * d;
      case "hours":
      case "hour":
      case "hrs":
      case "hr":
      case "h":
        return n * h;
      case "minutes":
      case "minute":
      case "mins":
      case "min":
      case "m":
        return n * m;
      case "seconds":
      case "second":
      case "secs":
      case "sec":
      case "s":
        return n * s;
      case "milliseconds":
      case "millisecond":
      case "msecs":
      case "msec":
      case "ms":
        return n;
      default:
        return void 0;
    }
  }
  function fmtShort(ms2) {
    var msAbs = Math.abs(ms2);
    if (msAbs >= d) {
      return Math.round(ms2 / d) + "d";
    }
    if (msAbs >= h) {
      return Math.round(ms2 / h) + "h";
    }
    if (msAbs >= m) {
      return Math.round(ms2 / m) + "m";
    }
    if (msAbs >= s) {
      return Math.round(ms2 / s) + "s";
    }
    return ms2 + "ms";
  }
  function fmtLong(ms2) {
    var msAbs = Math.abs(ms2);
    if (msAbs >= d) {
      return plural(ms2, msAbs, d, "day");
    }
    if (msAbs >= h) {
      return plural(ms2, msAbs, h, "hour");
    }
    if (msAbs >= m) {
      return plural(ms2, msAbs, m, "minute");
    }
    if (msAbs >= s) {
      return plural(ms2, msAbs, s, "second");
    }
    return ms2 + " ms";
  }
  function plural(ms2, msAbs, n, name) {
    var isPlural = msAbs >= n * 1.5;
    return Math.round(ms2 / n) + " " + name + (isPlural ? "s" : "");
  }
  return ms;
}
var common;
var hasRequiredCommon;
function requireCommon() {
  if (hasRequiredCommon) return common;
  hasRequiredCommon = 1;
  function setup(env) {
    createDebug.debug = createDebug;
    createDebug.default = createDebug;
    createDebug.coerce = coerce;
    createDebug.disable = disable;
    createDebug.enable = enable;
    createDebug.enabled = enabled;
    createDebug.humanize = requireMs();
    createDebug.destroy = destroy;
    Object.keys(env).forEach((key) => {
      createDebug[key] = env[key];
    });
    createDebug.names = [];
    createDebug.skips = [];
    createDebug.formatters = {};
    function selectColor(namespace) {
      let hash = 0;
      for (let i = 0; i < namespace.length; i++) {
        hash = (hash << 5) - hash + namespace.charCodeAt(i);
        hash |= 0;
      }
      return createDebug.colors[Math.abs(hash) % createDebug.colors.length];
    }
    createDebug.selectColor = selectColor;
    function createDebug(namespace) {
      let prevTime;
      let enableOverride = null;
      let namespacesCache;
      let enabledCache;
      function debug(...args) {
        if (!debug.enabled) {
          return;
        }
        const self2 = debug;
        const curr = Number(/* @__PURE__ */ new Date());
        const ms2 = curr - (prevTime || curr);
        self2.diff = ms2;
        self2.prev = prevTime;
        self2.curr = curr;
        prevTime = curr;
        args[0] = createDebug.coerce(args[0]);
        if (typeof args[0] !== "string") {
          args.unshift("%O");
        }
        let index = 0;
        args[0] = args[0].replace(/%([a-zA-Z%])/g, (match, format) => {
          if (match === "%%") {
            return "%";
          }
          index++;
          const formatter = createDebug.formatters[format];
          if (typeof formatter === "function") {
            const val = args[index];
            match = formatter.call(self2, val);
            args.splice(index, 1);
            index--;
          }
          return match;
        });
        createDebug.formatArgs.call(self2, args);
        const logFn = self2.log || createDebug.log;
        logFn.apply(self2, args);
      }
      debug.namespace = namespace;
      debug.useColors = createDebug.useColors();
      debug.color = createDebug.selectColor(namespace);
      debug.extend = extend2;
      debug.destroy = createDebug.destroy;
      Object.defineProperty(debug, "enabled", {
        enumerable: true,
        configurable: false,
        get: () => {
          if (enableOverride !== null) {
            return enableOverride;
          }
          if (namespacesCache !== createDebug.namespaces) {
            namespacesCache = createDebug.namespaces;
            enabledCache = createDebug.enabled(namespace);
          }
          return enabledCache;
        },
        set: (v) => {
          enableOverride = v;
        }
      });
      if (typeof createDebug.init === "function") {
        createDebug.init(debug);
      }
      return debug;
    }
    function extend2(namespace, delimiter) {
      const newDebug = createDebug(this.namespace + (typeof delimiter === "undefined" ? ":" : delimiter) + namespace);
      newDebug.log = this.log;
      return newDebug;
    }
    function enable(namespaces) {
      createDebug.save(namespaces);
      createDebug.namespaces = namespaces;
      createDebug.names = [];
      createDebug.skips = [];
      const split = (typeof namespaces === "string" ? namespaces : "").trim().replace(/\s+/g, ",").split(",").filter(Boolean);
      for (const ns of split) {
        if (ns[0] === "-") {
          createDebug.skips.push(ns.slice(1));
        } else {
          createDebug.names.push(ns);
        }
      }
    }
    function matchesTemplate(search, template) {
      let searchIndex = 0;
      let templateIndex = 0;
      let starIndex = -1;
      let matchIndex = 0;
      while (searchIndex < search.length) {
        if (templateIndex < template.length && (template[templateIndex] === search[searchIndex] || template[templateIndex] === "*")) {
          if (template[templateIndex] === "*") {
            starIndex = templateIndex;
            matchIndex = searchIndex;
            templateIndex++;
          } else {
            searchIndex++;
            templateIndex++;
          }
        } else if (starIndex !== -1) {
          templateIndex = starIndex + 1;
          matchIndex++;
          searchIndex = matchIndex;
        } else {
          return false;
        }
      }
      while (templateIndex < template.length && template[templateIndex] === "*") {
        templateIndex++;
      }
      return templateIndex === template.length;
    }
    function disable() {
      const namespaces = [
        ...createDebug.names,
        ...createDebug.skips.map((namespace) => "-" + namespace)
      ].join(",");
      createDebug.enable("");
      return namespaces;
    }
    function enabled(name) {
      for (const skip of createDebug.skips) {
        if (matchesTemplate(name, skip)) {
          return false;
        }
      }
      for (const ns of createDebug.names) {
        if (matchesTemplate(name, ns)) {
          return true;
        }
      }
      return false;
    }
    function coerce(val) {
      if (val instanceof Error) {
        return val.stack || val.message;
      }
      return val;
    }
    function destroy() {
      console.warn("Instance method `debug.destroy()` is deprecated and no longer does anything. It will be removed in the next major version of `debug`.");
    }
    createDebug.enable(createDebug.load());
    return createDebug;
  }
  common = setup;
  return common;
}
var hasRequiredBrowser;
function requireBrowser() {
  if (hasRequiredBrowser) return browser.exports;
  hasRequiredBrowser = 1;
  (function(module, exports$1) {
    exports$1.formatArgs = formatArgs;
    exports$1.save = save;
    exports$1.load = load;
    exports$1.useColors = useColors;
    exports$1.storage = localstorage();
    exports$1.destroy = /* @__PURE__ */ (() => {
      let warned = false;
      return () => {
        if (!warned) {
          warned = true;
          console.warn("Instance method `debug.destroy()` is deprecated and no longer does anything. It will be removed in the next major version of `debug`.");
        }
      };
    })();
    exports$1.colors = [
      "#0000CC",
      "#0000FF",
      "#0033CC",
      "#0033FF",
      "#0066CC",
      "#0066FF",
      "#0099CC",
      "#0099FF",
      "#00CC00",
      "#00CC33",
      "#00CC66",
      "#00CC99",
      "#00CCCC",
      "#00CCFF",
      "#3300CC",
      "#3300FF",
      "#3333CC",
      "#3333FF",
      "#3366CC",
      "#3366FF",
      "#3399CC",
      "#3399FF",
      "#33CC00",
      "#33CC33",
      "#33CC66",
      "#33CC99",
      "#33CCCC",
      "#33CCFF",
      "#6600CC",
      "#6600FF",
      "#6633CC",
      "#6633FF",
      "#66CC00",
      "#66CC33",
      "#9900CC",
      "#9900FF",
      "#9933CC",
      "#9933FF",
      "#99CC00",
      "#99CC33",
      "#CC0000",
      "#CC0033",
      "#CC0066",
      "#CC0099",
      "#CC00CC",
      "#CC00FF",
      "#CC3300",
      "#CC3333",
      "#CC3366",
      "#CC3399",
      "#CC33CC",
      "#CC33FF",
      "#CC6600",
      "#CC6633",
      "#CC9900",
      "#CC9933",
      "#CCCC00",
      "#CCCC33",
      "#FF0000",
      "#FF0033",
      "#FF0066",
      "#FF0099",
      "#FF00CC",
      "#FF00FF",
      "#FF3300",
      "#FF3333",
      "#FF3366",
      "#FF3399",
      "#FF33CC",
      "#FF33FF",
      "#FF6600",
      "#FF6633",
      "#FF9900",
      "#FF9933",
      "#FFCC00",
      "#FFCC33"
    ];
    function useColors() {
      if (typeof window !== "undefined" && window.process && (window.process.type === "renderer" || window.process.__nwjs)) {
        return true;
      }
      if (typeof navigator !== "undefined" && navigator.userAgent && navigator.userAgent.toLowerCase().match(/(edge|trident)\/(\d+)/)) {
        return false;
      }
      let m;
      return typeof document !== "undefined" && document.documentElement && document.documentElement.style && document.documentElement.style.WebkitAppearance || // Is firebug? http://stackoverflow.com/a/398120/376773
      typeof window !== "undefined" && window.console && (window.console.firebug || window.console.exception && window.console.table) || // Is firefox >= v31?
      // https://developer.mozilla.org/en-US/docs/Tools/Web_Console#Styling_messages
      typeof navigator !== "undefined" && navigator.userAgent && (m = navigator.userAgent.toLowerCase().match(/firefox\/(\d+)/)) && parseInt(m[1], 10) >= 31 || // Double check webkit in userAgent just in case we are in a worker
      typeof navigator !== "undefined" && navigator.userAgent && navigator.userAgent.toLowerCase().match(/applewebkit\/(\d+)/);
    }
    function formatArgs(args) {
      args[0] = (this.useColors ? "%c" : "") + this.namespace + (this.useColors ? " %c" : " ") + args[0] + (this.useColors ? "%c " : " ") + "+" + module.exports.humanize(this.diff);
      if (!this.useColors) {
        return;
      }
      const c = "color: " + this.color;
      args.splice(1, 0, c, "color: inherit");
      let index = 0;
      let lastC = 0;
      args[0].replace(/%[a-zA-Z%]/g, (match) => {
        if (match === "%%") {
          return;
        }
        index++;
        if (match === "%c") {
          lastC = index;
        }
      });
      args.splice(lastC, 0, c);
    }
    exports$1.log = console.debug || console.log || (() => {
    });
    function save(namespaces) {
      try {
        if (namespaces) {
          exports$1.storage.setItem("debug", namespaces);
        } else {
          exports$1.storage.removeItem("debug");
        }
      } catch (error) {
      }
    }
    function load() {
      let r;
      try {
        r = exports$1.storage.getItem("debug") || exports$1.storage.getItem("DEBUG");
      } catch (error) {
      }
      if (!r && typeof process !== "undefined" && "env" in process) {
        r = process.env.DEBUG;
      }
      return r;
    }
    function localstorage() {
      try {
        return localStorage;
      } catch (error) {
      }
    }
    module.exports = requireCommon()(exports$1);
    const { formatters } = module.exports;
    formatters.j = function(v) {
      try {
        return JSON.stringify(v);
      } catch (error) {
        return "[UnexpectedJSONParseError]: " + error.message;
      }
    };
  })(browser, browser.exports);
  return browser.exports;
}
var node = { exports: {} };
const require$$0 = /* @__PURE__ */ getAugmentedNamespace(tty$1);
var hasFlag;
var hasRequiredHasFlag;
function requireHasFlag() {
  if (hasRequiredHasFlag) return hasFlag;
  hasRequiredHasFlag = 1;
  hasFlag = (flag, argv = process.argv) => {
    const prefix = flag.startsWith("-") ? "" : flag.length === 1 ? "-" : "--";
    const position = argv.indexOf(prefix + flag);
    const terminatorPosition = argv.indexOf("--");
    return position !== -1 && (terminatorPosition === -1 || position < terminatorPosition);
  };
  return hasFlag;
}
var supportsColor_1;
var hasRequiredSupportsColor;
function requireSupportsColor() {
  if (hasRequiredSupportsColor) return supportsColor_1;
  hasRequiredSupportsColor = 1;
  const os = require$$0$2;
  const tty2 = require$$0;
  const hasFlag2 = requireHasFlag();
  const { env } = process;
  let forceColor;
  if (hasFlag2("no-color") || hasFlag2("no-colors") || hasFlag2("color=false") || hasFlag2("color=never")) {
    forceColor = 0;
  } else if (hasFlag2("color") || hasFlag2("colors") || hasFlag2("color=true") || hasFlag2("color=always")) {
    forceColor = 1;
  }
  if ("FORCE_COLOR" in env) {
    if (env.FORCE_COLOR === "true") {
      forceColor = 1;
    } else if (env.FORCE_COLOR === "false") {
      forceColor = 0;
    } else {
      forceColor = env.FORCE_COLOR.length === 0 ? 1 : Math.min(parseInt(env.FORCE_COLOR, 10), 3);
    }
  }
  function translateLevel(level) {
    if (level === 0) {
      return false;
    }
    return {
      level,
      hasBasic: true,
      has256: level >= 2,
      has16m: level >= 3
    };
  }
  function supportsColor(haveStream, streamIsTTY) {
    if (forceColor === 0) {
      return 0;
    }
    if (hasFlag2("color=16m") || hasFlag2("color=full") || hasFlag2("color=truecolor")) {
      return 3;
    }
    if (hasFlag2("color=256")) {
      return 2;
    }
    if (haveStream && !streamIsTTY && forceColor === void 0) {
      return 0;
    }
    const min2 = forceColor || 0;
    if (env.TERM === "dumb") {
      return min2;
    }
    if (process.platform === "win32") {
      const osRelease = os.release().split(".");
      if (Number(osRelease[0]) >= 10 && Number(osRelease[2]) >= 10586) {
        return Number(osRelease[2]) >= 14931 ? 3 : 2;
      }
      return 1;
    }
    if ("CI" in env) {
      if (["TRAVIS", "CIRCLECI", "APPVEYOR", "GITLAB_CI", "GITHUB_ACTIONS", "BUILDKITE"].some((sign2) => sign2 in env) || env.CI_NAME === "codeship") {
        return 1;
      }
      return min2;
    }
    if ("TEAMCITY_VERSION" in env) {
      return /^(9\.(0*[1-9]\d*)\.|\d{2,}\.)/.test(env.TEAMCITY_VERSION) ? 1 : 0;
    }
    if (env.COLORTERM === "truecolor") {
      return 3;
    }
    if ("TERM_PROGRAM" in env) {
      const version = parseInt((env.TERM_PROGRAM_VERSION || "").split(".")[0], 10);
      switch (env.TERM_PROGRAM) {
        case "iTerm.app":
          return version >= 3 ? 3 : 2;
        case "Apple_Terminal":
          return 2;
      }
    }
    if (/-256(color)?$/i.test(env.TERM)) {
      return 2;
    }
    if (/^screen|^xterm|^vt100|^vt220|^rxvt|color|ansi|cygwin|linux/i.test(env.TERM)) {
      return 1;
    }
    if ("COLORTERM" in env) {
      return 1;
    }
    return min2;
  }
  function getSupportLevel(stream2) {
    const level = supportsColor(stream2, stream2 && stream2.isTTY);
    return translateLevel(level);
  }
  supportsColor_1 = {
    supportsColor: getSupportLevel,
    stdout: translateLevel(supportsColor(true, tty2.isatty(1))),
    stderr: translateLevel(supportsColor(true, tty2.isatty(2)))
  };
  return supportsColor_1;
}
var hasRequiredNode;
function requireNode() {
  if (hasRequiredNode) return node.exports;
  hasRequiredNode = 1;
  (function(module, exports$1) {
    const tty2 = require$$0;
    const util = require$$1;
    exports$1.init = init;
    exports$1.log = log;
    exports$1.formatArgs = formatArgs;
    exports$1.save = save;
    exports$1.load = load;
    exports$1.useColors = useColors;
    exports$1.destroy = util.deprecate(
      () => {
      },
      "Instance method `debug.destroy()` is deprecated and no longer does anything. It will be removed in the next major version of `debug`."
    );
    exports$1.colors = [6, 2, 3, 4, 5, 1];
    try {
      const supportsColor = requireSupportsColor();
      if (supportsColor && (supportsColor.stderr || supportsColor).level >= 2) {
        exports$1.colors = [
          20,
          21,
          26,
          27,
          32,
          33,
          38,
          39,
          40,
          41,
          42,
          43,
          44,
          45,
          56,
          57,
          62,
          63,
          68,
          69,
          74,
          75,
          76,
          77,
          78,
          79,
          80,
          81,
          92,
          93,
          98,
          99,
          112,
          113,
          128,
          129,
          134,
          135,
          148,
          149,
          160,
          161,
          162,
          163,
          164,
          165,
          166,
          167,
          168,
          169,
          170,
          171,
          172,
          173,
          178,
          179,
          184,
          185,
          196,
          197,
          198,
          199,
          200,
          201,
          202,
          203,
          204,
          205,
          206,
          207,
          208,
          209,
          214,
          215,
          220,
          221
        ];
      }
    } catch (error) {
    }
    exports$1.inspectOpts = Object.keys(process.env).filter((key) => {
      return /^debug_/i.test(key);
    }).reduce((obj, key) => {
      const prop = key.substring(6).toLowerCase().replace(/_([a-z])/g, (_, k) => {
        return k.toUpperCase();
      });
      let val = process.env[key];
      if (/^(yes|on|true|enabled)$/i.test(val)) {
        val = true;
      } else if (/^(no|off|false|disabled)$/i.test(val)) {
        val = false;
      } else if (val === "null") {
        val = null;
      } else {
        val = Number(val);
      }
      obj[prop] = val;
      return obj;
    }, {});
    function useColors() {
      return "colors" in exports$1.inspectOpts ? Boolean(exports$1.inspectOpts.colors) : tty2.isatty(process.stderr.fd);
    }
    function formatArgs(args) {
      const { namespace: name, useColors: useColors2 } = this;
      if (useColors2) {
        const c = this.color;
        const colorCode = "\x1B[3" + (c < 8 ? c : "8;5;" + c);
        const prefix = `  ${colorCode};1m${name} \x1B[0m`;
        args[0] = prefix + args[0].split("\n").join("\n" + prefix);
        args.push(colorCode + "m+" + module.exports.humanize(this.diff) + "\x1B[0m");
      } else {
        args[0] = getDate() + name + " " + args[0];
      }
    }
    function getDate() {
      if (exports$1.inspectOpts.hideDate) {
        return "";
      }
      return (/* @__PURE__ */ new Date()).toISOString() + " ";
    }
    function log(...args) {
      return process.stderr.write(util.formatWithOptions(exports$1.inspectOpts, ...args) + "\n");
    }
    function save(namespaces) {
      if (namespaces) {
        process.env.DEBUG = namespaces;
      } else {
        delete process.env.DEBUG;
      }
    }
    function load() {
      return process.env.DEBUG;
    }
    function init(debug) {
      debug.inspectOpts = {};
      const keys = Object.keys(exports$1.inspectOpts);
      for (let i = 0; i < keys.length; i++) {
        debug.inspectOpts[keys[i]] = exports$1.inspectOpts[keys[i]];
      }
    }
    module.exports = requireCommon()(exports$1);
    const { formatters } = module.exports;
    formatters.o = function(v) {
      this.inspectOpts.colors = this.useColors;
      return util.inspect(v, this.inspectOpts).split("\n").map((str) => str.trim()).join(" ");
    };
    formatters.O = function(v) {
      this.inspectOpts.colors = this.useColors;
      return util.inspect(v, this.inspectOpts);
    };
  })(node, node.exports);
  return node.exports;
}
var hasRequiredSrc;
function requireSrc() {
  if (hasRequiredSrc) return src.exports;
  hasRequiredSrc = 1;
  if (typeof process === "undefined" || process.type === "renderer" || process.browser === true || process.__nwjs) {
    src.exports = requireBrowser();
  } else {
    src.exports = requireNode();
  }
  return src.exports;
}
var debug_1;
var hasRequiredDebug;
function requireDebug() {
  if (hasRequiredDebug) return debug_1;
  hasRequiredDebug = 1;
  var debug;
  debug_1 = function() {
    if (!debug) {
      try {
        debug = requireSrc()("follow-redirects");
      } catch (error) {
      }
      if (typeof debug !== "function") {
        debug = function() {
        };
      }
    }
    debug.apply(null, arguments);
  };
  return debug_1;
}
var hasRequiredFollowRedirects;
function requireFollowRedirects() {
  if (hasRequiredFollowRedirects) return followRedirects$1.exports;
  hasRequiredFollowRedirects = 1;
  var url = require$$5;
  var URL2 = url.URL;
  var http = require$$3;
  var https = require$$4;
  var Writable = stream.Writable;
  var assert = require$$4$1;
  var debug = requireDebug();
  (function detectUnsupportedEnvironment() {
    var looksLikeNode = typeof process !== "undefined";
    var looksLikeBrowser = typeof window !== "undefined" && typeof document !== "undefined";
    var looksLikeV8 = isFunction2(Error.captureStackTrace);
    if (!looksLikeNode && (looksLikeBrowser || !looksLikeV8)) {
      console.warn("The follow-redirects package should be excluded from browser builds.");
    }
  })();
  var useNativeURL = false;
  try {
    assert(new URL2(""));
  } catch (error) {
    useNativeURL = error.code === "ERR_INVALID_URL";
  }
  var sensitiveHeaders = [
    "Authorization",
    "Proxy-Authorization",
    "Cookie"
  ];
  var preservedUrlFields = [
    "auth",
    "host",
    "hostname",
    "href",
    "path",
    "pathname",
    "port",
    "protocol",
    "query",
    "search",
    "hash"
  ];
  var events = ["abort", "aborted", "connect", "error", "socket", "timeout"];
  var eventHandlers = /* @__PURE__ */ Object.create(null);
  events.forEach(function(event) {
    eventHandlers[event] = function(arg1, arg2, arg3) {
      this._redirectable.emit(event, arg1, arg2, arg3);
    };
  });
  var InvalidUrlError = createErrorType(
    "ERR_INVALID_URL",
    "Invalid URL",
    TypeError
  );
  var RedirectionError = createErrorType(
    "ERR_FR_REDIRECTION_FAILURE",
    "Redirected request failed"
  );
  var TooManyRedirectsError = createErrorType(
    "ERR_FR_TOO_MANY_REDIRECTS",
    "Maximum number of redirects exceeded",
    RedirectionError
  );
  var MaxBodyLengthExceededError = createErrorType(
    "ERR_FR_MAX_BODY_LENGTH_EXCEEDED",
    "Request body larger than maxBodyLength limit"
  );
  var WriteAfterEndError = createErrorType(
    "ERR_STREAM_WRITE_AFTER_END",
    "write after end"
  );
  var destroy = Writable.prototype.destroy || noop2;
  function RedirectableRequest(options, responseCallback) {
    Writable.call(this);
    this._sanitizeOptions(options);
    this._options = options;
    this._ended = false;
    this._ending = false;
    this._redirectCount = 0;
    this._redirects = [];
    this._requestBodyLength = 0;
    this._requestBodyBuffers = [];
    if (responseCallback) {
      this.on("response", responseCallback);
    }
    var self2 = this;
    this._onNativeResponse = function(response) {
      try {
        self2._processResponse(response);
      } catch (cause) {
        self2.emit("error", cause instanceof RedirectionError ? cause : new RedirectionError({ cause }));
      }
    };
    this._headerFilter = new RegExp("^(?:" + sensitiveHeaders.concat(options.sensitiveHeaders).map(escapeRegex).join("|") + ")$", "i");
    this._performRequest();
  }
  RedirectableRequest.prototype = Object.create(Writable.prototype);
  RedirectableRequest.prototype.abort = function() {
    destroyRequest(this._currentRequest);
    this._currentRequest.abort();
    this.emit("abort");
  };
  RedirectableRequest.prototype.destroy = function(error) {
    destroyRequest(this._currentRequest, error);
    destroy.call(this, error);
    return this;
  };
  RedirectableRequest.prototype.write = function(data, encoding, callback) {
    if (this._ending) {
      throw new WriteAfterEndError();
    }
    if (!isString2(data) && !isBuffer2(data)) {
      throw new TypeError("data should be a string, Buffer or Uint8Array");
    }
    if (isFunction2(encoding)) {
      callback = encoding;
      encoding = null;
    }
    if (data.length === 0) {
      if (callback) {
        callback();
      }
      return;
    }
    if (this._requestBodyLength + data.length <= this._options.maxBodyLength) {
      this._requestBodyLength += data.length;
      this._requestBodyBuffers.push({ data, encoding });
      this._currentRequest.write(data, encoding, callback);
    } else {
      this.emit("error", new MaxBodyLengthExceededError());
      this.abort();
    }
  };
  RedirectableRequest.prototype.end = function(data, encoding, callback) {
    if (isFunction2(data)) {
      callback = data;
      data = encoding = null;
    } else if (isFunction2(encoding)) {
      callback = encoding;
      encoding = null;
    }
    if (!data) {
      this._ended = this._ending = true;
      this._currentRequest.end(null, null, callback);
    } else {
      var self2 = this;
      var currentRequest = this._currentRequest;
      this.write(data, encoding, function() {
        self2._ended = true;
        currentRequest.end(null, null, callback);
      });
      this._ending = true;
    }
  };
  RedirectableRequest.prototype.setHeader = function(name, value) {
    this._options.headers[name] = value;
    this._currentRequest.setHeader(name, value);
  };
  RedirectableRequest.prototype.removeHeader = function(name) {
    delete this._options.headers[name];
    this._currentRequest.removeHeader(name);
  };
  RedirectableRequest.prototype.setTimeout = function(msecs, callback) {
    var self2 = this;
    function destroyOnTimeout(socket) {
      socket.setTimeout(msecs);
      socket.removeListener("timeout", socket.destroy);
      socket.addListener("timeout", socket.destroy);
    }
    function startTimer(socket) {
      if (self2._timeout) {
        clearTimeout(self2._timeout);
      }
      self2._timeout = setTimeout(function() {
        self2.emit("timeout");
        clearTimer();
      }, msecs);
      destroyOnTimeout(socket);
    }
    function clearTimer() {
      if (self2._timeout) {
        clearTimeout(self2._timeout);
        self2._timeout = null;
      }
      self2.removeListener("abort", clearTimer);
      self2.removeListener("error", clearTimer);
      self2.removeListener("response", clearTimer);
      self2.removeListener("close", clearTimer);
      if (callback) {
        self2.removeListener("timeout", callback);
      }
      if (!self2.socket) {
        self2._currentRequest.removeListener("socket", startTimer);
      }
    }
    if (callback) {
      this.on("timeout", callback);
    }
    if (this.socket) {
      startTimer(this.socket);
    } else {
      this._currentRequest.once("socket", startTimer);
    }
    this.on("socket", destroyOnTimeout);
    this.on("abort", clearTimer);
    this.on("error", clearTimer);
    this.on("response", clearTimer);
    this.on("close", clearTimer);
    return this;
  };
  [
    "flushHeaders",
    "getHeader",
    "setNoDelay",
    "setSocketKeepAlive"
  ].forEach(function(method) {
    RedirectableRequest.prototype[method] = function(a, b) {
      return this._currentRequest[method](a, b);
    };
  });
  ["aborted", "connection", "socket"].forEach(function(property) {
    Object.defineProperty(RedirectableRequest.prototype, property, {
      get: function() {
        return this._currentRequest[property];
      }
    });
  });
  RedirectableRequest.prototype._sanitizeOptions = function(options) {
    if (!options.headers) {
      options.headers = {};
    }
    if (!isArray2(options.sensitiveHeaders)) {
      options.sensitiveHeaders = [];
    }
    if (options.host) {
      if (!options.hostname) {
        options.hostname = options.host;
      }
      delete options.host;
    }
    if (!options.pathname && options.path) {
      var searchPos = options.path.indexOf("?");
      if (searchPos < 0) {
        options.pathname = options.path;
      } else {
        options.pathname = options.path.substring(0, searchPos);
        options.search = options.path.substring(searchPos);
      }
    }
  };
  RedirectableRequest.prototype._performRequest = function() {
    var protocol = this._options.protocol;
    var nativeProtocol = this._options.nativeProtocols[protocol];
    if (!nativeProtocol) {
      throw new TypeError("Unsupported protocol " + protocol);
    }
    if (this._options.agents) {
      var scheme = protocol.slice(0, -1);
      this._options.agent = this._options.agents[scheme];
    }
    var request = this._currentRequest = nativeProtocol.request(this._options, this._onNativeResponse);
    request._redirectable = this;
    for (var event of events) {
      request.on(event, eventHandlers[event]);
    }
    this._currentUrl = /^\//.test(this._options.path) ? url.format(this._options) : (
      // When making a request to a proxy, […]
      // a client MUST send the target URI in absolute-form […].
      this._options.path
    );
    if (this._isRedirect) {
      var i = 0;
      var self2 = this;
      var buffers = this._requestBodyBuffers;
      (function writeNext(error) {
        if (request === self2._currentRequest) {
          if (error) {
            self2.emit("error", error);
          } else if (i < buffers.length) {
            var buffer = buffers[i++];
            if (!request.finished) {
              request.write(buffer.data, buffer.encoding, writeNext);
            }
          } else if (self2._ended) {
            request.end();
          }
        }
      })();
    }
  };
  RedirectableRequest.prototype._processResponse = function(response) {
    var statusCode = response.statusCode;
    if (this._options.trackRedirects) {
      this._redirects.push({
        url: this._currentUrl,
        headers: response.headers,
        statusCode
      });
    }
    var location = response.headers.location;
    if (!location || this._options.followRedirects === false || statusCode < 300 || statusCode >= 400) {
      response.responseUrl = this._currentUrl;
      response.redirects = this._redirects;
      this.emit("response", response);
      this._requestBodyBuffers = [];
      return;
    }
    destroyRequest(this._currentRequest);
    response.destroy();
    if (++this._redirectCount > this._options.maxRedirects) {
      throw new TooManyRedirectsError();
    }
    var requestHeaders;
    var beforeRedirect = this._options.beforeRedirect;
    if (beforeRedirect) {
      requestHeaders = Object.assign({
        // The Host header was set by nativeProtocol.request
        Host: response.req.getHeader("host")
      }, this._options.headers);
    }
    var method = this._options.method;
    if ((statusCode === 301 || statusCode === 302) && this._options.method === "POST" || // RFC7231§6.4.4: The 303 (See Other) status code indicates that
    // the server is redirecting the user agent to a different resource […]
    // A user agent can perform a retrieval request targeting that URI
    // (a GET or HEAD request if using HTTP) […]
    statusCode === 303 && !/^(?:GET|HEAD)$/.test(this._options.method)) {
      this._options.method = "GET";
      this._requestBodyBuffers = [];
      removeMatchingHeaders(/^content-/i, this._options.headers);
    }
    var currentHostHeader = removeMatchingHeaders(/^host$/i, this._options.headers);
    var currentUrlParts = parseUrl2(this._currentUrl);
    var currentHost = currentHostHeader || currentUrlParts.host;
    var currentUrl = /^\w+:/.test(location) ? this._currentUrl : url.format(Object.assign(currentUrlParts, { host: currentHost }));
    var redirectUrl = resolveUrl(location, currentUrl);
    debug("redirecting to", redirectUrl.href);
    this._isRedirect = true;
    spreadUrlObject(redirectUrl, this._options);
    if (redirectUrl.protocol !== currentUrlParts.protocol && redirectUrl.protocol !== "https:" || redirectUrl.host !== currentHost && !isSubdomain(redirectUrl.host, currentHost)) {
      removeMatchingHeaders(this._headerFilter, this._options.headers);
    }
    if (isFunction2(beforeRedirect)) {
      var responseDetails = {
        headers: response.headers,
        statusCode
      };
      var requestDetails = {
        url: currentUrl,
        method,
        headers: requestHeaders
      };
      beforeRedirect(this._options, responseDetails, requestDetails);
      this._sanitizeOptions(this._options);
    }
    this._performRequest();
  };
  function wrap(protocols) {
    var exports$1 = {
      maxRedirects: 21,
      maxBodyLength: 10 * 1024 * 1024
    };
    var nativeProtocols = {};
    Object.keys(protocols).forEach(function(scheme) {
      var protocol = scheme + ":";
      var nativeProtocol = nativeProtocols[protocol] = protocols[scheme];
      var wrappedProtocol = exports$1[scheme] = Object.create(nativeProtocol);
      function request(input, options, callback) {
        if (isURL(input)) {
          input = spreadUrlObject(input);
        } else if (isString2(input)) {
          input = spreadUrlObject(parseUrl2(input));
        } else {
          callback = options;
          options = validateUrl(input);
          input = { protocol };
        }
        if (isFunction2(options)) {
          callback = options;
          options = null;
        }
        options = Object.assign({
          maxRedirects: exports$1.maxRedirects,
          maxBodyLength: exports$1.maxBodyLength
        }, input, options);
        options.nativeProtocols = nativeProtocols;
        if (!isString2(options.host) && !isString2(options.hostname)) {
          options.hostname = "::1";
        }
        assert.equal(options.protocol, protocol, "protocol mismatch");
        debug("options", options);
        return new RedirectableRequest(options, callback);
      }
      function get2(input, options, callback) {
        var wrappedRequest = wrappedProtocol.request(input, options, callback);
        wrappedRequest.end();
        return wrappedRequest;
      }
      Object.defineProperties(wrappedProtocol, {
        request: { value: request, configurable: true, enumerable: true, writable: true },
        get: { value: get2, configurable: true, enumerable: true, writable: true }
      });
    });
    return exports$1;
  }
  function noop2() {
  }
  function parseUrl2(input) {
    var parsed;
    if (useNativeURL) {
      parsed = new URL2(input);
    } else {
      parsed = validateUrl(url.parse(input));
      if (!isString2(parsed.protocol)) {
        throw new InvalidUrlError({ input });
      }
    }
    return parsed;
  }
  function resolveUrl(relative, base) {
    return useNativeURL ? new URL2(relative, base) : parseUrl2(url.resolve(base, relative));
  }
  function validateUrl(input) {
    if (/^\[/.test(input.hostname) && !/^\[[:0-9a-f]+\]$/i.test(input.hostname)) {
      throw new InvalidUrlError({ input: input.href || input });
    }
    if (/^\[/.test(input.host) && !/^\[[:0-9a-f]+\](:\d+)?$/i.test(input.host)) {
      throw new InvalidUrlError({ input: input.href || input });
    }
    return input;
  }
  function spreadUrlObject(urlObject, target) {
    var spread2 = target || {};
    for (var key of preservedUrlFields) {
      spread2[key] = urlObject[key];
    }
    if (spread2.hostname.startsWith("[")) {
      spread2.hostname = spread2.hostname.slice(1, -1);
    }
    if (spread2.port !== "") {
      spread2.port = Number(spread2.port);
    }
    spread2.path = spread2.search ? spread2.pathname + spread2.search : spread2.pathname;
    return spread2;
  }
  function removeMatchingHeaders(regex, headers) {
    var lastValue;
    for (var header in headers) {
      if (regex.test(header)) {
        lastValue = headers[header];
        delete headers[header];
      }
    }
    return lastValue === null || typeof lastValue === "undefined" ? void 0 : String(lastValue).trim();
  }
  function createErrorType(code, message, baseClass) {
    function CustomError(properties) {
      if (isFunction2(Error.captureStackTrace)) {
        Error.captureStackTrace(this, this.constructor);
      }
      Object.assign(this, properties || {});
      this.code = code;
      this.message = this.cause ? message + ": " + this.cause.message : message;
    }
    CustomError.prototype = new (baseClass || Error)();
    Object.defineProperties(CustomError.prototype, {
      constructor: {
        value: CustomError,
        enumerable: false
      },
      name: {
        value: "Error [" + code + "]",
        enumerable: false
      }
    });
    return CustomError;
  }
  function destroyRequest(request, error) {
    for (var event of events) {
      request.removeListener(event, eventHandlers[event]);
    }
    request.on("error", noop2);
    request.destroy(error);
  }
  function isSubdomain(subdomain, domain) {
    assert(isString2(subdomain) && isString2(domain));
    var dot = subdomain.length - domain.length - 1;
    return dot > 0 && subdomain[dot] === "." && subdomain.endsWith(domain);
  }
  function isArray2(value) {
    return value instanceof Array;
  }
  function isString2(value) {
    return typeof value === "string" || value instanceof String;
  }
  function isFunction2(value) {
    return typeof value === "function";
  }
  function isBuffer2(value) {
    return typeof value === "object" && "length" in value;
  }
  function isURL(value) {
    return URL2 && value instanceof URL2;
  }
  function escapeRegex(regex) {
    return regex.replace(/[\]\\/()*+?.$]/g, "\\$&");
  }
  followRedirects$1.exports = wrap({ http, https });
  followRedirects$1.exports.wrap = wrap;
  return followRedirects$1.exports;
}
var followRedirectsExports = requireFollowRedirects();
const followRedirects = /* @__PURE__ */ getDefaultExportFromCjs(followRedirectsExports);
const VERSION$1 = "1.16.0";
function parseProtocol(url) {
  const match = /^([-+\w]{1,25}):(?:\/\/)?/.exec(url);
  return match && match[1] || "";
}
const DATA_URL_PATTERN = /^(?:([^;]+);)?(?:[^;]+;)?(base64|),([\s\S]*)$/;
function fromDataURI(uri2, asBlob, options) {
  const _Blob = options && options.Blob || platform.classes.Blob;
  const protocol = parseProtocol(uri2);
  if (asBlob === void 0 && _Blob) {
    asBlob = true;
  }
  if (protocol === "data") {
    uri2 = protocol.length ? uri2.slice(protocol.length + 1) : uri2;
    const match = DATA_URL_PATTERN.exec(uri2);
    if (!match) {
      throw new AxiosError$1("Invalid URL", AxiosError$1.ERR_INVALID_URL);
    }
    const mime = match[1];
    const isBase64 = match[2];
    const body = match[3];
    const buffer = Buffer.from(decodeURIComponent(body), isBase64 ? "base64" : "utf8");
    if (asBlob) {
      if (!_Blob) {
        throw new AxiosError$1("Blob is not supported", AxiosError$1.ERR_NOT_SUPPORT);
      }
      return new _Blob([buffer], { type: mime });
    }
    return buffer;
  }
  throw new AxiosError$1("Unsupported protocol " + protocol, AxiosError$1.ERR_NOT_SUPPORT);
}
const kInternals = /* @__PURE__ */ Symbol("internals");
class AxiosTransformStream extends stream.Transform {
  constructor(options) {
    options = utils$1.toFlatObject(
      options,
      {
        maxRate: 0,
        chunkSize: 64 * 1024,
        minChunkSize: 100,
        timeWindow: 500,
        ticksRate: 2,
        samplesCount: 15
      },
      null,
      (prop, source) => {
        return !utils$1.isUndefined(source[prop]);
      }
    );
    super({
      readableHighWaterMark: options.chunkSize
    });
    const internals = this[kInternals] = {
      timeWindow: options.timeWindow,
      chunkSize: options.chunkSize,
      maxRate: options.maxRate,
      minChunkSize: options.minChunkSize,
      bytesSeen: 0,
      isCaptured: false,
      notifiedBytesLoaded: 0,
      ts: Date.now(),
      bytes: 0,
      onReadCallback: null
    };
    this.on("newListener", (event) => {
      if (event === "progress") {
        if (!internals.isCaptured) {
          internals.isCaptured = true;
        }
      }
    });
  }
  _read(size) {
    const internals = this[kInternals];
    if (internals.onReadCallback) {
      internals.onReadCallback();
    }
    return super._read(size);
  }
  _transform(chunk, encoding, callback) {
    const internals = this[kInternals];
    const maxRate = internals.maxRate;
    const readableHighWaterMark = this.readableHighWaterMark;
    const timeWindow = internals.timeWindow;
    const divider = 1e3 / timeWindow;
    const bytesThreshold = maxRate / divider;
    const minChunkSize = internals.minChunkSize !== false ? Math.max(internals.minChunkSize, bytesThreshold * 0.01) : 0;
    const pushChunk = (_chunk, _callback) => {
      const bytes = Buffer.byteLength(_chunk);
      internals.bytesSeen += bytes;
      internals.bytes += bytes;
      internals.isCaptured && this.emit("progress", internals.bytesSeen);
      if (this.push(_chunk)) {
        process.nextTick(_callback);
      } else {
        internals.onReadCallback = () => {
          internals.onReadCallback = null;
          process.nextTick(_callback);
        };
      }
    };
    const transformChunk = (_chunk, _callback) => {
      const chunkSize = Buffer.byteLength(_chunk);
      let chunkRemainder = null;
      let maxChunkSize = readableHighWaterMark;
      let bytesLeft;
      let passed = 0;
      if (maxRate) {
        const now = Date.now();
        if (!internals.ts || (passed = now - internals.ts) >= timeWindow) {
          internals.ts = now;
          bytesLeft = bytesThreshold - internals.bytes;
          internals.bytes = bytesLeft < 0 ? -bytesLeft : 0;
          passed = 0;
        }
        bytesLeft = bytesThreshold - internals.bytes;
      }
      if (maxRate) {
        if (bytesLeft <= 0) {
          return setTimeout(() => {
            _callback(null, _chunk);
          }, timeWindow - passed);
        }
        if (bytesLeft < maxChunkSize) {
          maxChunkSize = bytesLeft;
        }
      }
      if (maxChunkSize && chunkSize > maxChunkSize && chunkSize - maxChunkSize > minChunkSize) {
        chunkRemainder = _chunk.subarray(maxChunkSize);
        _chunk = _chunk.subarray(0, maxChunkSize);
      }
      pushChunk(
        _chunk,
        chunkRemainder ? () => {
          process.nextTick(_callback, null, chunkRemainder);
        } : _callback
      );
    };
    transformChunk(chunk, function transformNextChunk(err, _chunk) {
      if (err) {
        return callback(err);
      }
      if (_chunk) {
        transformChunk(_chunk, transformNextChunk);
      } else {
        callback(null);
      }
    });
  }
}
const { asyncIterator } = Symbol;
const readBlob = async function* (blob) {
  if (blob.stream) {
    yield* blob.stream();
  } else if (blob.arrayBuffer) {
    yield await blob.arrayBuffer();
  } else if (blob[asyncIterator]) {
    yield* blob[asyncIterator]();
  } else {
    yield blob;
  }
};
const BOUNDARY_ALPHABET = platform.ALPHABET.ALPHA_DIGIT + "-_";
const textEncoder = typeof TextEncoder === "function" ? new TextEncoder() : new require$$1.TextEncoder();
const CRLF = "\r\n";
const CRLF_BYTES = textEncoder.encode(CRLF);
const CRLF_BYTES_COUNT = 2;
class FormDataPart {
  constructor(name, value) {
    const { escapeName } = this.constructor;
    const isStringValue = utils$1.isString(value);
    let headers = `Content-Disposition: form-data; name="${escapeName(name)}"${!isStringValue && value.name ? `; filename="${escapeName(value.name)}"` : ""}${CRLF}`;
    if (isStringValue) {
      value = textEncoder.encode(String(value).replace(/\r?\n|\r\n?/g, CRLF));
    } else {
      const safeType = String(value.type || "application/octet-stream").replace(/[\r\n]/g, "");
      headers += `Content-Type: ${safeType}${CRLF}`;
    }
    this.headers = textEncoder.encode(headers + CRLF);
    this.contentLength = isStringValue ? value.byteLength : value.size;
    this.size = this.headers.byteLength + this.contentLength + CRLF_BYTES_COUNT;
    this.name = name;
    this.value = value;
  }
  async *encode() {
    yield this.headers;
    const { value } = this;
    if (utils$1.isTypedArray(value)) {
      yield value;
    } else {
      yield* readBlob(value);
    }
    yield CRLF_BYTES;
  }
  static escapeName(name) {
    return String(name).replace(
      /[\r\n"]/g,
      (match) => ({
        "\r": "%0D",
        "\n": "%0A",
        '"': "%22"
      })[match]
    );
  }
}
const formDataToStream = (form, headersHandler, options) => {
  const {
    tag = "form-data-boundary",
    size = 25,
    boundary = tag + "-" + platform.generateString(size, BOUNDARY_ALPHABET)
  } = options || {};
  if (!utils$1.isFormData(form)) {
    throw TypeError("FormData instance required");
  }
  if (boundary.length < 1 || boundary.length > 70) {
    throw Error("boundary must be 1-70 characters long");
  }
  const boundaryBytes = textEncoder.encode("--" + boundary + CRLF);
  const footerBytes = textEncoder.encode("--" + boundary + "--" + CRLF);
  let contentLength = footerBytes.byteLength;
  const parts = Array.from(form.entries()).map(([name, value]) => {
    const part = new FormDataPart(name, value);
    contentLength += part.size;
    return part;
  });
  contentLength += boundaryBytes.byteLength * parts.length;
  contentLength = utils$1.toFiniteNumber(contentLength);
  const computedHeaders = {
    "Content-Type": `multipart/form-data; boundary=${boundary}`
  };
  if (Number.isFinite(contentLength)) {
    computedHeaders["Content-Length"] = contentLength;
  }
  headersHandler && headersHandler(computedHeaders);
  return Readable.from(
    (async function* () {
      for (const part of parts) {
        yield boundaryBytes;
        yield* part.encode();
      }
      yield footerBytes;
    })()
  );
};
class ZlibHeaderTransformStream extends stream.Transform {
  __transform(chunk, encoding, callback) {
    this.push(chunk);
    callback();
  }
  _transform(chunk, encoding, callback) {
    if (chunk.length !== 0) {
      this._transform = this.__transform;
      if (chunk[0] !== 120) {
        const header = Buffer.alloc(2);
        header[0] = 120;
        header[1] = 156;
        this.push(header, encoding);
      }
    }
    this.__transform(chunk, encoding, callback);
  }
}
const callbackify = (fn, reducer) => {
  return utils$1.isAsyncFn(fn) ? function(...args) {
    const cb = args.pop();
    fn.apply(this, args).then((value) => {
      try {
        reducer ? cb(null, ...reducer(value)) : cb(null, value);
      } catch (err) {
        cb(err);
      }
    }, cb);
  } : fn;
};
const LOOPBACK_HOSTNAMES = /* @__PURE__ */ new Set(["localhost"]);
const isIPv4Loopback = (host) => {
  const parts = host.split(".");
  if (parts.length !== 4) return false;
  if (parts[0] !== "127") return false;
  return parts.every((p) => /^\d+$/.test(p) && Number(p) >= 0 && Number(p) <= 255);
};
const isIPv6Loopback = (host) => {
  if (host === "::1") return true;
  const v4MappedDotted = host.match(/^::ffff:(\d+\.\d+\.\d+\.\d+)$/i);
  if (v4MappedDotted) return isIPv4Loopback(v4MappedDotted[1]);
  const v4MappedHex = host.match(/^::ffff:([0-9a-f]{1,4}):([0-9a-f]{1,4})$/i);
  if (v4MappedHex) {
    const high = parseInt(v4MappedHex[1], 16);
    return high >= 32512 && high <= 32767;
  }
  const groups = host.split(":");
  if (groups.length === 8) {
    for (let i = 0; i < 7; i++) {
      if (!/^0+$/.test(groups[i])) return false;
    }
    return /^0*1$/.test(groups[7]);
  }
  return false;
};
const isLoopback = (host) => {
  if (!host) return false;
  if (LOOPBACK_HOSTNAMES.has(host)) return true;
  if (isIPv4Loopback(host)) return true;
  return isIPv6Loopback(host);
};
const DEFAULT_PORTS = {
  http: 80,
  https: 443,
  ws: 80,
  wss: 443,
  ftp: 21
};
const parseNoProxyEntry = (entry) => {
  let entryHost = entry;
  let entryPort = 0;
  if (entryHost.charAt(0) === "[") {
    const bracketIndex = entryHost.indexOf("]");
    if (bracketIndex !== -1) {
      const host = entryHost.slice(1, bracketIndex);
      const rest = entryHost.slice(bracketIndex + 1);
      if (rest.charAt(0) === ":" && /^\d+$/.test(rest.slice(1))) {
        entryPort = Number.parseInt(rest.slice(1), 10);
      }
      return [host, entryPort];
    }
  }
  const firstColon = entryHost.indexOf(":");
  const lastColon = entryHost.lastIndexOf(":");
  if (firstColon !== -1 && firstColon === lastColon && /^\d+$/.test(entryHost.slice(lastColon + 1))) {
    entryPort = Number.parseInt(entryHost.slice(lastColon + 1), 10);
    entryHost = entryHost.slice(0, lastColon);
  }
  return [entryHost, entryPort];
};
const IPV4_MAPPED_DOTTED_RE = /^(?:::|(?:0{1,4}:){1,4}:|(?:0{1,4}:){5})ffff:(\d+\.\d+\.\d+\.\d+)$/i;
const IPV4_MAPPED_HEX_RE = /^(?:::|(?:0{1,4}:){1,4}:|(?:0{1,4}:){5})ffff:([0-9a-f]{1,4}):([0-9a-f]{1,4})$/i;
const unmapIPv4MappedIPv6 = (host) => {
  if (typeof host !== "string" || host.indexOf(":") === -1) return host;
  const dotted = host.match(IPV4_MAPPED_DOTTED_RE);
  if (dotted) return dotted[1];
  const hex = host.match(IPV4_MAPPED_HEX_RE);
  if (hex) {
    const high = parseInt(hex[1], 16);
    const low = parseInt(hex[2], 16);
    return `${high >> 8}.${high & 255}.${low >> 8}.${low & 255}`;
  }
  return host;
};
const normalizeNoProxyHost = (hostname) => {
  if (!hostname) {
    return hostname;
  }
  if (hostname.charAt(0) === "[" && hostname.charAt(hostname.length - 1) === "]") {
    hostname = hostname.slice(1, -1);
  }
  return unmapIPv4MappedIPv6(hostname.replace(/\.+$/, ""));
};
function shouldBypassProxy(location) {
  let parsed;
  try {
    parsed = new URL(location);
  } catch (_err) {
    return false;
  }
  const noProxy = (process.env.no_proxy || process.env.NO_PROXY || "").toLowerCase();
  if (!noProxy) {
    return false;
  }
  if (noProxy === "*") {
    return true;
  }
  const port = Number.parseInt(parsed.port, 10) || DEFAULT_PORTS[parsed.protocol.split(":", 1)[0]] || 0;
  const hostname = normalizeNoProxyHost(parsed.hostname.toLowerCase());
  return noProxy.split(/[\s,]+/).some((entry) => {
    if (!entry) {
      return false;
    }
    let [entryHost, entryPort] = parseNoProxyEntry(entry);
    entryHost = normalizeNoProxyHost(entryHost);
    if (!entryHost) {
      return false;
    }
    if (entryPort && entryPort !== port) {
      return false;
    }
    if (entryHost.charAt(0) === "*") {
      entryHost = entryHost.slice(1);
    }
    if (entryHost.charAt(0) === ".") {
      return hostname.endsWith(entryHost);
    }
    return hostname === entryHost || isLoopback(hostname) && isLoopback(entryHost);
  });
}
function speedometer(samplesCount, min2) {
  samplesCount = samplesCount || 10;
  const bytes = new Array(samplesCount);
  const timestamps = new Array(samplesCount);
  let head = 0;
  let tail = 0;
  let firstSampleTS;
  min2 = min2 !== void 0 ? min2 : 1e3;
  return function push(chunkLength) {
    const now = Date.now();
    const startedAt = timestamps[tail];
    if (!firstSampleTS) {
      firstSampleTS = now;
    }
    bytes[head] = chunkLength;
    timestamps[head] = now;
    let i = tail;
    let bytesCount = 0;
    while (i !== head) {
      bytesCount += bytes[i++];
      i = i % samplesCount;
    }
    head = (head + 1) % samplesCount;
    if (head === tail) {
      tail = (tail + 1) % samplesCount;
    }
    if (now - firstSampleTS < min2) {
      return;
    }
    const passed = startedAt && now - startedAt;
    return passed ? Math.round(bytesCount * 1e3 / passed) : void 0;
  };
}
function throttle(fn, freq) {
  let timestamp = 0;
  let threshold = 1e3 / freq;
  let lastArgs;
  let timer;
  const invoke = (args, now = Date.now()) => {
    timestamp = now;
    lastArgs = null;
    if (timer) {
      clearTimeout(timer);
      timer = null;
    }
    fn(...args);
  };
  const throttled = (...args) => {
    const now = Date.now();
    const passed = now - timestamp;
    if (passed >= threshold) {
      invoke(args, now);
    } else {
      lastArgs = args;
      if (!timer) {
        timer = setTimeout(() => {
          timer = null;
          invoke(lastArgs);
        }, threshold - passed);
      }
    }
  };
  const flush = () => lastArgs && invoke(lastArgs);
  return [throttled, flush];
}
const progressEventReducer = (listener, isDownloadStream, freq = 3) => {
  let bytesNotified = 0;
  const _speedometer = speedometer(50, 250);
  return throttle((e) => {
    const rawLoaded = e.loaded;
    const total = e.lengthComputable ? e.total : void 0;
    const loaded = total != null ? Math.min(rawLoaded, total) : rawLoaded;
    const progressBytes = Math.max(0, loaded - bytesNotified);
    const rate = _speedometer(progressBytes);
    bytesNotified = Math.max(bytesNotified, loaded);
    const data = {
      loaded,
      total,
      progress: total ? loaded / total : void 0,
      bytes: progressBytes,
      rate: rate ? rate : void 0,
      estimated: rate && total ? (total - loaded) / rate : void 0,
      event: e,
      lengthComputable: total != null,
      [isDownloadStream ? "download" : "upload"]: true
    };
    listener(data);
  }, freq);
};
const progressEventDecorator = (total, throttled) => {
  const lengthComputable = total != null;
  return [
    (loaded) => throttled[0]({
      lengthComputable,
      total,
      loaded
    }),
    throttled[1]
  ];
};
const asyncDecorator = (fn) => (...args) => utils$1.asap(() => fn(...args));
function estimateDataURLDecodedBytes(url) {
  if (!url || typeof url !== "string") return 0;
  if (!url.startsWith("data:")) return 0;
  const comma = url.indexOf(",");
  if (comma < 0) return 0;
  const meta = url.slice(5, comma);
  const body = url.slice(comma + 1);
  const isBase64 = /;base64/i.test(meta);
  if (isBase64) {
    let effectiveLen = body.length;
    const len = body.length;
    for (let i = 0; i < len; i++) {
      if (body.charCodeAt(i) === 37 && i + 2 < len) {
        const a = body.charCodeAt(i + 1);
        const b = body.charCodeAt(i + 2);
        const isHex = (a >= 48 && a <= 57 || a >= 65 && a <= 70 || a >= 97 && a <= 102) && (b >= 48 && b <= 57 || b >= 65 && b <= 70 || b >= 97 && b <= 102);
        if (isHex) {
          effectiveLen -= 2;
          i += 2;
        }
      }
    }
    let pad = 0;
    let idx = len - 1;
    const tailIsPct3D = (j) => j >= 2 && body.charCodeAt(j - 2) === 37 && // '%'
    body.charCodeAt(j - 1) === 51 && // '3'
    (body.charCodeAt(j) === 68 || body.charCodeAt(j) === 100);
    if (idx >= 0) {
      if (body.charCodeAt(idx) === 61) {
        pad++;
        idx--;
      } else if (tailIsPct3D(idx)) {
        pad++;
        idx -= 3;
      }
    }
    if (pad === 1 && idx >= 0) {
      if (body.charCodeAt(idx) === 61) {
        pad++;
      } else if (tailIsPct3D(idx)) {
        pad++;
      }
    }
    const groups = Math.floor(effectiveLen / 4);
    const bytes2 = groups * 3 - (pad || 0);
    return bytes2 > 0 ? bytes2 : 0;
  }
  if (typeof Buffer !== "undefined" && typeof Buffer.byteLength === "function") {
    return Buffer.byteLength(body, "utf8");
  }
  let bytes = 0;
  for (let i = 0, len = body.length; i < len; i++) {
    const c = body.charCodeAt(i);
    if (c < 128) {
      bytes += 1;
    } else if (c < 2048) {
      bytes += 2;
    } else if (c >= 55296 && c <= 56319 && i + 1 < len) {
      const next = body.charCodeAt(i + 1);
      if (next >= 56320 && next <= 57343) {
        bytes += 4;
        i++;
      } else {
        bytes += 3;
      }
    } else {
      bytes += 3;
    }
  }
  return bytes;
}
const zlibOptions = {
  flush: zlib.constants.Z_SYNC_FLUSH,
  finishFlush: zlib.constants.Z_SYNC_FLUSH
};
const brotliOptions = {
  flush: zlib.constants.BROTLI_OPERATION_FLUSH,
  finishFlush: zlib.constants.BROTLI_OPERATION_FLUSH
};
const isBrotliSupported = utils$1.isFunction(zlib.createBrotliDecompress);
const { http: httpFollow, https: httpsFollow } = followRedirects;
const isHttps = /https:?/;
const FORM_DATA_CONTENT_HEADERS$1 = ["content-type", "content-length"];
function setFormDataHeaders$1(headers, formHeaders, policy) {
  if (policy !== "content-only") {
    headers.set(formHeaders);
    return;
  }
  Object.entries(formHeaders).forEach(([key, val]) => {
    if (FORM_DATA_CONTENT_HEADERS$1.includes(key.toLowerCase())) {
      headers.set(key, val);
    }
  });
}
const kAxiosSocketListener = /* @__PURE__ */ Symbol("axios.http.socketListener");
const kAxiosCurrentReq = /* @__PURE__ */ Symbol("axios.http.currentReq");
const supportedProtocols = platform.protocols.map((protocol) => {
  return protocol + ":";
});
const decodeURIComponentSafe = (value) => {
  if (!utils$1.isString(value)) {
    return value;
  }
  try {
    return decodeURIComponent(value);
  } catch (error) {
    return value;
  }
};
const flushOnFinish = (stream2, [throttled, flush]) => {
  stream2.on("end", flush).on("error", flush);
  return throttled;
};
class Http2Sessions {
  constructor() {
    this.sessions = /* @__PURE__ */ Object.create(null);
  }
  getSession(authority, options) {
    options = Object.assign(
      {
        sessionTimeout: 1e3
      },
      options
    );
    let authoritySessions = this.sessions[authority];
    if (authoritySessions) {
      let len = authoritySessions.length;
      for (let i = 0; i < len; i++) {
        const [sessionHandle, sessionOptions] = authoritySessions[i];
        if (!sessionHandle.destroyed && !sessionHandle.closed && require$$1.isDeepStrictEqual(sessionOptions, options)) {
          return sessionHandle;
        }
      }
    }
    const session = http2.connect(authority, options);
    let removed;
    const removeSession = () => {
      if (removed) {
        return;
      }
      removed = true;
      let entries = authoritySessions, len = entries.length, i = len;
      while (i--) {
        if (entries[i][0] === session) {
          if (len === 1) {
            delete this.sessions[authority];
          } else {
            entries.splice(i, 1);
          }
          if (!session.closed) {
            session.close();
          }
          return;
        }
      }
    };
    const originalRequestFn = session.request;
    const { sessionTimeout } = options;
    if (sessionTimeout != null) {
      let timer;
      let streamsCount = 0;
      session.request = function() {
        const stream2 = originalRequestFn.apply(this, arguments);
        streamsCount++;
        if (timer) {
          clearTimeout(timer);
          timer = null;
        }
        stream2.once("close", () => {
          if (!--streamsCount) {
            timer = setTimeout(() => {
              timer = null;
              removeSession();
            }, sessionTimeout);
          }
        });
        return stream2;
      };
    }
    session.once("close", removeSession);
    let entry = [session, options];
    authoritySessions ? authoritySessions.push(entry) : authoritySessions = this.sessions[authority] = [entry];
    return session;
  }
}
const http2Sessions = new Http2Sessions();
function dispatchBeforeRedirect(options, responseDetails, requestDetails) {
  if (options.beforeRedirects.proxy) {
    options.beforeRedirects.proxy(options);
  }
  if (options.beforeRedirects.config) {
    options.beforeRedirects.config(options, responseDetails, requestDetails);
  }
}
function setProxy(options, configProxy, location, isRedirect2) {
  let proxy = configProxy;
  if (!proxy && proxy !== false) {
    const proxyUrl = getProxyForUrl(location);
    if (proxyUrl) {
      if (!shouldBypassProxy(location)) {
        proxy = new URL(proxyUrl);
      }
    }
  }
  if (isRedirect2 && options.headers) {
    for (const name of Object.keys(options.headers)) {
      if (name.toLowerCase() === "proxy-authorization") {
        delete options.headers[name];
      }
    }
  }
  if (proxy) {
    const isProxyURL = proxy instanceof URL;
    const readProxyField = (key) => isProxyURL || utils$1.hasOwnProp(proxy, key) ? proxy[key] : void 0;
    const proxyUsername = readProxyField("username");
    const proxyPassword = readProxyField("password");
    let proxyAuth = utils$1.hasOwnProp(proxy, "auth") ? proxy.auth : void 0;
    if (proxyUsername) {
      proxyAuth = (proxyUsername || "") + ":" + (proxyPassword || "");
    }
    if (proxyAuth) {
      const authIsObject = typeof proxyAuth === "object";
      const authUsername = authIsObject && utils$1.hasOwnProp(proxyAuth, "username") ? proxyAuth.username : void 0;
      const authPassword = authIsObject && utils$1.hasOwnProp(proxyAuth, "password") ? proxyAuth.password : void 0;
      const validProxyAuth = Boolean(authUsername || authPassword);
      if (validProxyAuth) {
        proxyAuth = (authUsername || "") + ":" + (authPassword || "");
      } else if (authIsObject) {
        throw new AxiosError$1("Invalid proxy authorization", AxiosError$1.ERR_BAD_OPTION, { proxy });
      }
      const base64 = Buffer.from(proxyAuth, "utf8").toString("base64");
      options.headers["Proxy-Authorization"] = "Basic " + base64;
    }
    let hasUserHostHeader = false;
    for (const name of Object.keys(options.headers)) {
      if (name.toLowerCase() === "host") {
        hasUserHostHeader = true;
        break;
      }
    }
    if (!hasUserHostHeader) {
      options.headers.host = options.hostname + (options.port ? ":" + options.port : "");
    }
    const proxyHost = readProxyField("hostname") || readProxyField("host");
    options.hostname = proxyHost;
    options.host = proxyHost;
    options.port = readProxyField("port");
    options.path = location;
    const proxyProtocol = readProxyField("protocol");
    if (proxyProtocol) {
      options.protocol = proxyProtocol.includes(":") ? proxyProtocol : `${proxyProtocol}:`;
    }
  }
  options.beforeRedirects.proxy = function beforeRedirect(redirectOptions) {
    setProxy(redirectOptions, configProxy, redirectOptions.href, true);
  };
}
const isHttpAdapterSupported = typeof process !== "undefined" && utils$1.kindOf(process) === "process";
const wrapAsync = (asyncExecutor) => {
  return new Promise((resolve2, reject) => {
    let onDone;
    let isDone;
    const done = (value, isRejected) => {
      if (isDone) return;
      isDone = true;
      onDone && onDone(value, isRejected);
    };
    const _resolve = (value) => {
      done(value);
      resolve2(value);
    };
    const _reject = (reason) => {
      done(reason, true);
      reject(reason);
    };
    asyncExecutor(_resolve, _reject, (onDoneHandler) => onDone = onDoneHandler).catch(_reject);
  });
};
const resolveFamily = ({ address, family }) => {
  if (!utils$1.isString(address)) {
    throw TypeError("address must be a string");
  }
  return {
    address,
    family: family || (address.indexOf(".") < 0 ? 6 : 4)
  };
};
const buildAddressEntry = (address, family) => resolveFamily(utils$1.isObject(address) ? address : { address, family });
const http2Transport = {
  request(options, cb) {
    const authority = options.protocol + "//" + options.hostname + ":" + (options.port || (options.protocol === "https:" ? 443 : 80));
    const { http2Options, headers } = options;
    const session = http2Sessions.getSession(authority, http2Options);
    const { HTTP2_HEADER_SCHEME, HTTP2_HEADER_METHOD, HTTP2_HEADER_PATH, HTTP2_HEADER_STATUS } = http2.constants;
    const http2Headers = {
      [HTTP2_HEADER_SCHEME]: options.protocol.replace(":", ""),
      [HTTP2_HEADER_METHOD]: options.method,
      [HTTP2_HEADER_PATH]: options.path
    };
    utils$1.forEach(headers, (header, name) => {
      name.charAt(0) !== ":" && (http2Headers[name] = header);
    });
    const req = session.request(http2Headers);
    req.once("response", (responseHeaders) => {
      const response = req;
      responseHeaders = Object.assign({}, responseHeaders);
      const status = responseHeaders[HTTP2_HEADER_STATUS];
      delete responseHeaders[HTTP2_HEADER_STATUS];
      response.headers = responseHeaders;
      response.statusCode = +status;
      cb(response);
    });
    return req;
  }
};
const httpAdapter = isHttpAdapterSupported && function httpAdapter2(config) {
  return wrapAsync(async function dispatchHttpRequest(resolve$1, reject, onDone) {
    const own2 = (key) => utils$1.hasOwnProp(config, key) ? config[key] : void 0;
    let data = own2("data");
    let lookup = own2("lookup");
    let family = own2("family");
    let httpVersion = own2("httpVersion");
    if (httpVersion === void 0) httpVersion = 1;
    let http2Options = own2("http2Options");
    const responseType = own2("responseType");
    const responseEncoding = own2("responseEncoding");
    const method = config.method.toUpperCase();
    let isDone;
    let rejected = false;
    let req;
    let connectPhaseTimer;
    httpVersion = +httpVersion;
    if (Number.isNaN(httpVersion)) {
      throw TypeError(`Invalid protocol version: '${config.httpVersion}' is not a number`);
    }
    if (httpVersion !== 1 && httpVersion !== 2) {
      throw TypeError(`Unsupported protocol version '${httpVersion}'`);
    }
    const isHttp2 = httpVersion === 2;
    if (lookup) {
      const _lookup = callbackify(lookup, (value) => utils$1.isArray(value) ? value : [value]);
      lookup = (hostname, opt, cb) => {
        _lookup(hostname, opt, (err, arg0, arg1) => {
          if (err) {
            return cb(err);
          }
          const addresses = utils$1.isArray(arg0) ? arg0.map((addr) => buildAddressEntry(addr)) : [buildAddressEntry(arg0, arg1)];
          opt.all ? cb(err, addresses) : cb(err, addresses[0].address, addresses[0].family);
        });
      };
    }
    const abortEmitter = new EventEmitter();
    function abort(reason) {
      try {
        abortEmitter.emit(
          "abort",
          !reason || reason.type ? new CanceledError$1(null, config, req) : reason
        );
      } catch (err) {
        console.warn("emit error", err);
      }
    }
    function clearConnectPhaseTimer() {
      if (connectPhaseTimer) {
        clearTimeout(connectPhaseTimer);
        connectPhaseTimer = null;
      }
    }
    function createTimeoutError() {
      let timeoutErrorMessage = config.timeout ? "timeout of " + config.timeout + "ms exceeded" : "timeout exceeded";
      const transitional2 = config.transitional || transitionalDefaults;
      if (config.timeoutErrorMessage) {
        timeoutErrorMessage = config.timeoutErrorMessage;
      }
      return new AxiosError$1(
        timeoutErrorMessage,
        transitional2.clarifyTimeoutError ? AxiosError$1.ETIMEDOUT : AxiosError$1.ECONNABORTED,
        config,
        req
      );
    }
    abortEmitter.once("abort", reject);
    const onFinished = () => {
      clearConnectPhaseTimer();
      if (config.cancelToken) {
        config.cancelToken.unsubscribe(abort);
      }
      if (config.signal) {
        config.signal.removeEventListener("abort", abort);
      }
      abortEmitter.removeAllListeners();
    };
    if (config.cancelToken || config.signal) {
      config.cancelToken && config.cancelToken.subscribe(abort);
      if (config.signal) {
        config.signal.aborted ? abort() : config.signal.addEventListener("abort", abort);
      }
    }
    onDone((response, isRejected) => {
      isDone = true;
      clearConnectPhaseTimer();
      if (isRejected) {
        rejected = true;
        onFinished();
        return;
      }
      const { data: data2 } = response;
      if (data2 instanceof stream.Readable || data2 instanceof stream.Duplex) {
        const offListeners = stream.finished(data2, () => {
          offListeners();
          onFinished();
        });
      } else {
        onFinished();
      }
    });
    const fullPath = buildFullPath(config.baseURL, config.url, config.allowAbsoluteUrls);
    const parsed = new URL(fullPath, platform.hasBrowserEnv ? platform.origin : void 0);
    const protocol = parsed.protocol || supportedProtocols[0];
    if (protocol === "data:") {
      if (config.maxContentLength > -1) {
        const dataUrl = String(config.url || fullPath || "");
        const estimated = estimateDataURLDecodedBytes(dataUrl);
        if (estimated > config.maxContentLength) {
          return reject(
            new AxiosError$1(
              "maxContentLength size of " + config.maxContentLength + " exceeded",
              AxiosError$1.ERR_BAD_RESPONSE,
              config
            )
          );
        }
      }
      let convertedData;
      if (method !== "GET") {
        return settle(resolve$1, reject, {
          status: 405,
          statusText: "method not allowed",
          headers: {},
          config
        });
      }
      try {
        convertedData = fromDataURI(config.url, responseType === "blob", {
          Blob: config.env && config.env.Blob
        });
      } catch (err) {
        throw AxiosError$1.from(err, AxiosError$1.ERR_BAD_REQUEST, config);
      }
      if (responseType === "text") {
        convertedData = convertedData.toString(responseEncoding);
        if (!responseEncoding || responseEncoding === "utf8") {
          convertedData = utils$1.stripBOM(convertedData);
        }
      } else if (responseType === "stream") {
        convertedData = stream.Readable.from(convertedData);
      }
      return settle(resolve$1, reject, {
        data: convertedData,
        status: 200,
        statusText: "OK",
        headers: new AxiosHeaders$1(),
        config
      });
    }
    if (supportedProtocols.indexOf(protocol) === -1) {
      return reject(
        new AxiosError$1("Unsupported protocol " + protocol, AxiosError$1.ERR_BAD_REQUEST, config)
      );
    }
    const headers = AxiosHeaders$1.from(config.headers).normalize();
    headers.set("User-Agent", "axios/" + VERSION$1, false);
    const { onUploadProgress, onDownloadProgress } = config;
    const maxRate = config.maxRate;
    let maxUploadRate = void 0;
    let maxDownloadRate = void 0;
    if (utils$1.isSpecCompliantForm(data)) {
      const userBoundary = headers.getContentType(/boundary=([-_\w\d]{10,70})/i);
      data = formDataToStream(
        data,
        (formHeaders) => {
          headers.set(formHeaders);
        },
        {
          tag: `axios-${VERSION$1}-boundary`,
          boundary: userBoundary && userBoundary[1] || void 0
        }
      );
    } else if (utils$1.isFormData(data) && utils$1.isFunction(data.getHeaders) && data.getHeaders !== Object.prototype.getHeaders) {
      setFormDataHeaders$1(headers, data.getHeaders(), own2("formDataHeaderPolicy"));
      if (!headers.hasContentLength()) {
        try {
          const knownLength = await require$$1.promisify(data.getLength).call(data);
          Number.isFinite(knownLength) && knownLength >= 0 && headers.setContentLength(knownLength);
        } catch (e) {
        }
      }
    } else if (utils$1.isBlob(data) || utils$1.isFile(data)) {
      data.size && headers.setContentType(data.type || "application/octet-stream");
      headers.setContentLength(data.size || 0);
      data = stream.Readable.from(readBlob(data));
    } else if (data && !utils$1.isStream(data)) {
      if (Buffer.isBuffer(data)) ;
      else if (utils$1.isArrayBuffer(data)) {
        data = Buffer.from(new Uint8Array(data));
      } else if (utils$1.isString(data)) {
        data = Buffer.from(data, "utf-8");
      } else {
        return reject(
          new AxiosError$1(
            "Data after transformation must be a string, an ArrayBuffer, a Buffer, or a Stream",
            AxiosError$1.ERR_BAD_REQUEST,
            config
          )
        );
      }
      headers.setContentLength(data.length, false);
      if (config.maxBodyLength > -1 && data.length > config.maxBodyLength) {
        return reject(
          new AxiosError$1(
            "Request body larger than maxBodyLength limit",
            AxiosError$1.ERR_BAD_REQUEST,
            config
          )
        );
      }
    }
    const contentLength = utils$1.toFiniteNumber(headers.getContentLength());
    if (utils$1.isArray(maxRate)) {
      maxUploadRate = maxRate[0];
      maxDownloadRate = maxRate[1];
    } else {
      maxUploadRate = maxDownloadRate = maxRate;
    }
    if (data && (onUploadProgress || maxUploadRate)) {
      if (!utils$1.isStream(data)) {
        data = stream.Readable.from(data, { objectMode: false });
      }
      data = stream.pipeline(
        [
          data,
          new AxiosTransformStream({
            maxRate: utils$1.toFiniteNumber(maxUploadRate)
          })
        ],
        utils$1.noop
      );
      onUploadProgress && data.on(
        "progress",
        flushOnFinish(
          data,
          progressEventDecorator(
            contentLength,
            progressEventReducer(asyncDecorator(onUploadProgress), false, 3)
          )
        )
      );
    }
    let auth = void 0;
    const configAuth = own2("auth");
    if (configAuth) {
      const username = configAuth.username || "";
      const password = configAuth.password || "";
      auth = username + ":" + password;
    }
    if (!auth && parsed.username) {
      const urlUsername = decodeURIComponentSafe(parsed.username);
      const urlPassword = decodeURIComponentSafe(parsed.password);
      auth = urlUsername + ":" + urlPassword;
    }
    auth && headers.delete("authorization");
    let path;
    try {
      path = buildURL(
        parsed.pathname + parsed.search,
        config.params,
        config.paramsSerializer
      ).replace(/^\?/, "");
    } catch (err) {
      const customErr = new Error(err.message);
      customErr.config = config;
      customErr.url = config.url;
      customErr.exists = true;
      return reject(customErr);
    }
    headers.set(
      "Accept-Encoding",
      "gzip, compress, deflate" + (isBrotliSupported ? ", br" : ""),
      false
    );
    const options = Object.assign(/* @__PURE__ */ Object.create(null), {
      path,
      method,
      headers: headers.toJSON(),
      agents: { http: config.httpAgent, https: config.httpsAgent },
      auth,
      protocol,
      family,
      beforeRedirect: dispatchBeforeRedirect,
      beforeRedirects: /* @__PURE__ */ Object.create(null),
      http2Options
    });
    !utils$1.isUndefined(lookup) && (options.lookup = lookup);
    if (config.socketPath) {
      if (typeof config.socketPath !== "string") {
        return reject(
          new AxiosError$1("socketPath must be a string", AxiosError$1.ERR_BAD_OPTION_VALUE, config)
        );
      }
      if (config.allowedSocketPaths != null) {
        const allowed = Array.isArray(config.allowedSocketPaths) ? config.allowedSocketPaths : [config.allowedSocketPaths];
        const resolvedSocket = resolve(config.socketPath);
        const isAllowed = allowed.some(
          (entry) => typeof entry === "string" && resolve(entry) === resolvedSocket
        );
        if (!isAllowed) {
          return reject(
            new AxiosError$1(
              `socketPath "${config.socketPath}" is not permitted by allowedSocketPaths`,
              AxiosError$1.ERR_BAD_OPTION_VALUE,
              config
            )
          );
        }
      }
      options.socketPath = config.socketPath;
    } else {
      options.hostname = parsed.hostname.startsWith("[") ? parsed.hostname.slice(1, -1) : parsed.hostname;
      options.port = parsed.port;
      setProxy(
        options,
        config.proxy,
        protocol + "//" + parsed.hostname + (parsed.port ? ":" + parsed.port : "") + options.path
      );
    }
    let transport;
    let isNativeTransport = false;
    const isHttpsRequest = isHttps.test(options.protocol);
    options.agent = isHttpsRequest ? config.httpsAgent : config.httpAgent;
    if (isHttp2) {
      transport = http2Transport;
    } else {
      const configTransport = own2("transport");
      if (configTransport) {
        transport = configTransport;
      } else if (config.maxRedirects === 0) {
        transport = isHttpsRequest ? require$$4 : require$$3;
        isNativeTransport = true;
      } else {
        if (config.maxRedirects) {
          options.maxRedirects = config.maxRedirects;
        }
        const configBeforeRedirect = own2("beforeRedirect");
        if (configBeforeRedirect) {
          options.beforeRedirects.config = configBeforeRedirect;
        }
        transport = isHttpsRequest ? httpsFollow : httpFollow;
      }
    }
    if (config.maxBodyLength > -1) {
      options.maxBodyLength = config.maxBodyLength;
    } else {
      options.maxBodyLength = Infinity;
    }
    options.insecureHTTPParser = Boolean(own2("insecureHTTPParser"));
    req = transport.request(options, function handleResponse(res) {
      clearConnectPhaseTimer();
      if (req.destroyed) return;
      const streams = [res];
      const responseLength = utils$1.toFiniteNumber(res.headers["content-length"]);
      if (onDownloadProgress || maxDownloadRate) {
        const transformStream = new AxiosTransformStream({
          maxRate: utils$1.toFiniteNumber(maxDownloadRate)
        });
        onDownloadProgress && transformStream.on(
          "progress",
          flushOnFinish(
            transformStream,
            progressEventDecorator(
              responseLength,
              progressEventReducer(asyncDecorator(onDownloadProgress), true, 3)
            )
          )
        );
        streams.push(transformStream);
      }
      let responseStream = res;
      const lastRequest = res.req || req;
      if (config.decompress !== false && res.headers["content-encoding"]) {
        if (method === "HEAD" || res.statusCode === 204) {
          delete res.headers["content-encoding"];
        }
        switch ((res.headers["content-encoding"] || "").toLowerCase()) {
          /*eslint default-case:0*/
          case "gzip":
          case "x-gzip":
          case "compress":
          case "x-compress":
            streams.push(zlib.createUnzip(zlibOptions));
            delete res.headers["content-encoding"];
            break;
          case "deflate":
            streams.push(new ZlibHeaderTransformStream());
            streams.push(zlib.createUnzip(zlibOptions));
            delete res.headers["content-encoding"];
            break;
          case "br":
            if (isBrotliSupported) {
              streams.push(zlib.createBrotliDecompress(brotliOptions));
              delete res.headers["content-encoding"];
            }
        }
      }
      responseStream = streams.length > 1 ? stream.pipeline(streams, utils$1.noop) : streams[0];
      const response = {
        status: res.statusCode,
        statusText: res.statusMessage,
        headers: new AxiosHeaders$1(res.headers),
        config,
        request: lastRequest
      };
      if (responseType === "stream") {
        if (config.maxContentLength > -1) {
          const limit = config.maxContentLength;
          const source = responseStream;
          async function* enforceMaxContentLength() {
            let totalResponseBytes = 0;
            for await (const chunk of source) {
              totalResponseBytes += chunk.length;
              if (totalResponseBytes > limit) {
                throw new AxiosError$1(
                  "maxContentLength size of " + limit + " exceeded",
                  AxiosError$1.ERR_BAD_RESPONSE,
                  config,
                  lastRequest
                );
              }
              yield chunk;
            }
          }
          responseStream = stream.Readable.from(enforceMaxContentLength(), {
            objectMode: false
          });
        }
        response.data = responseStream;
        settle(resolve$1, reject, response);
      } else {
        const responseBuffer = [];
        let totalResponseBytes = 0;
        responseStream.on("data", function handleStreamData(chunk) {
          responseBuffer.push(chunk);
          totalResponseBytes += chunk.length;
          if (config.maxContentLength > -1 && totalResponseBytes > config.maxContentLength) {
            rejected = true;
            responseStream.destroy();
            abort(
              new AxiosError$1(
                "maxContentLength size of " + config.maxContentLength + " exceeded",
                AxiosError$1.ERR_BAD_RESPONSE,
                config,
                lastRequest
              )
            );
          }
        });
        responseStream.on("aborted", function handlerStreamAborted() {
          if (rejected) {
            return;
          }
          const err = new AxiosError$1(
            "stream has been aborted",
            AxiosError$1.ERR_BAD_RESPONSE,
            config,
            lastRequest,
            response
          );
          responseStream.destroy(err);
          reject(err);
        });
        responseStream.on("error", function handleStreamError(err) {
          if (rejected) return;
          reject(AxiosError$1.from(err, null, config, lastRequest, response));
        });
        responseStream.on("end", function handleStreamEnd() {
          try {
            let responseData = responseBuffer.length === 1 ? responseBuffer[0] : Buffer.concat(responseBuffer);
            if (responseType !== "arraybuffer") {
              responseData = responseData.toString(responseEncoding);
              if (!responseEncoding || responseEncoding === "utf8") {
                responseData = utils$1.stripBOM(responseData);
              }
            }
            response.data = responseData;
          } catch (err) {
            return reject(AxiosError$1.from(err, null, config, response.request, response));
          }
          settle(resolve$1, reject, response);
        });
      }
      abortEmitter.once("abort", (err) => {
        if (!responseStream.destroyed) {
          responseStream.emit("error", err);
          responseStream.destroy();
        }
      });
    });
    abortEmitter.once("abort", (err) => {
      if (req.close) {
        req.close();
      } else {
        req.destroy(err);
      }
    });
    req.on("error", function handleRequestError(err) {
      reject(AxiosError$1.from(err, null, config, req));
    });
    const boundSockets = /* @__PURE__ */ new Set();
    req.on("socket", function handleRequestSocket(socket) {
      socket.setKeepAlive(true, 1e3 * 60);
      if (!socket[kAxiosSocketListener]) {
        socket.on("error", function handleSocketError(err) {
          const current = socket[kAxiosCurrentReq];
          if (current && !current.destroyed) {
            current.destroy(err);
          }
        });
        socket[kAxiosSocketListener] = true;
      }
      socket[kAxiosCurrentReq] = req;
      boundSockets.add(socket);
    });
    req.once("close", function clearCurrentReq() {
      clearConnectPhaseTimer();
      for (const socket of boundSockets) {
        if (socket[kAxiosCurrentReq] === req) {
          socket[kAxiosCurrentReq] = null;
        }
      }
      boundSockets.clear();
    });
    if (config.timeout) {
      const timeout = parseInt(config.timeout, 10);
      if (Number.isNaN(timeout)) {
        abort(
          new AxiosError$1(
            "error trying to parse `config.timeout` to int",
            AxiosError$1.ERR_BAD_OPTION_VALUE,
            config,
            req
          )
        );
        return;
      }
      const handleTimeout = function handleTimeout2() {
        if (isDone) return;
        abort(createTimeoutError());
      };
      if (isNativeTransport && timeout > 0) {
        connectPhaseTimer = setTimeout(handleTimeout, timeout);
      }
      req.setTimeout(timeout, handleTimeout);
    } else {
      req.setTimeout(0);
    }
    if (utils$1.isStream(data)) {
      let ended = false;
      let errored = false;
      data.on("end", () => {
        ended = true;
      });
      data.once("error", (err) => {
        errored = true;
        req.destroy(err);
      });
      data.on("close", () => {
        if (!ended && !errored) {
          abort(new CanceledError$1("Request stream has been aborted", config, req));
        }
      });
      let uploadStream = data;
      if (config.maxBodyLength > -1 && config.maxRedirects === 0) {
        const limit = config.maxBodyLength;
        let bytesSent = 0;
        uploadStream = stream.pipeline(
          [
            data,
            new stream.Transform({
              transform(chunk, _enc, cb) {
                bytesSent += chunk.length;
                if (bytesSent > limit) {
                  return cb(
                    new AxiosError$1(
                      "Request body larger than maxBodyLength limit",
                      AxiosError$1.ERR_BAD_REQUEST,
                      config,
                      req
                    )
                  );
                }
                cb(null, chunk);
              }
            })
          ],
          utils$1.noop
        );
        uploadStream.on("error", (err) => {
          if (!req.destroyed) req.destroy(err);
        });
      }
      uploadStream.pipe(req);
    } else {
      data && req.write(data);
      req.end();
    }
  });
};
const isURLSameOrigin = platform.hasStandardBrowserEnv ? /* @__PURE__ */ ((origin2, isMSIE) => (url) => {
  url = new URL(url, platform.origin);
  return origin2.protocol === url.protocol && origin2.host === url.host && (isMSIE || origin2.port === url.port);
})(
  new URL(platform.origin),
  platform.navigator && /(msie|trident)/i.test(platform.navigator.userAgent)
) : () => true;
const cookies = platform.hasStandardBrowserEnv ? (
  // Standard browser envs support document.cookie
  {
    write(name, value, expires, path, domain, secure, sameSite) {
      if (typeof document === "undefined") return;
      const cookie = [`${name}=${encodeURIComponent(value)}`];
      if (utils$1.isNumber(expires)) {
        cookie.push(`expires=${new Date(expires).toUTCString()}`);
      }
      if (utils$1.isString(path)) {
        cookie.push(`path=${path}`);
      }
      if (utils$1.isString(domain)) {
        cookie.push(`domain=${domain}`);
      }
      if (secure === true) {
        cookie.push("secure");
      }
      if (utils$1.isString(sameSite)) {
        cookie.push(`SameSite=${sameSite}`);
      }
      document.cookie = cookie.join("; ");
    },
    read(name) {
      if (typeof document === "undefined") return null;
      const cookies2 = document.cookie.split(";");
      for (let i = 0; i < cookies2.length; i++) {
        const cookie = cookies2[i].replace(/^\s+/, "");
        const eq = cookie.indexOf("=");
        if (eq !== -1 && cookie.slice(0, eq) === name) {
          return decodeURIComponent(cookie.slice(eq + 1));
        }
      }
      return null;
    },
    remove(name) {
      this.write(name, "", Date.now() - 864e5, "/");
    }
  }
) : (
  // Non-standard browser env (web workers, react-native) lack needed support.
  {
    write() {
    },
    read() {
      return null;
    },
    remove() {
    }
  }
);
const headersToObject = (thing) => thing instanceof AxiosHeaders$1 ? { ...thing } : thing;
function mergeConfig$1(config1, config2) {
  config2 = config2 || {};
  const config = /* @__PURE__ */ Object.create(null);
  Object.defineProperty(config, "hasOwnProperty", {
    // Null-proto descriptor so a polluted Object.prototype.get cannot turn
    // this data descriptor into an accessor descriptor on the way in.
    __proto__: null,
    value: Object.prototype.hasOwnProperty,
    enumerable: false,
    writable: true,
    configurable: true
  });
  function getMergedValue(target, source, prop, caseless) {
    if (utils$1.isPlainObject(target) && utils$1.isPlainObject(source)) {
      return utils$1.merge.call({ caseless }, target, source);
    } else if (utils$1.isPlainObject(source)) {
      return utils$1.merge({}, source);
    } else if (utils$1.isArray(source)) {
      return source.slice();
    }
    return source;
  }
  function mergeDeepProperties(a, b, prop, caseless) {
    if (!utils$1.isUndefined(b)) {
      return getMergedValue(a, b, prop, caseless);
    } else if (!utils$1.isUndefined(a)) {
      return getMergedValue(void 0, a, prop, caseless);
    }
  }
  function valueFromConfig2(a, b) {
    if (!utils$1.isUndefined(b)) {
      return getMergedValue(void 0, b);
    }
  }
  function defaultToConfig2(a, b) {
    if (!utils$1.isUndefined(b)) {
      return getMergedValue(void 0, b);
    } else if (!utils$1.isUndefined(a)) {
      return getMergedValue(void 0, a);
    }
  }
  function mergeDirectKeys(a, b, prop) {
    if (utils$1.hasOwnProp(config2, prop)) {
      return getMergedValue(a, b);
    } else if (utils$1.hasOwnProp(config1, prop)) {
      return getMergedValue(void 0, a);
    }
  }
  const mergeMap = {
    url: valueFromConfig2,
    method: valueFromConfig2,
    data: valueFromConfig2,
    baseURL: defaultToConfig2,
    transformRequest: defaultToConfig2,
    transformResponse: defaultToConfig2,
    paramsSerializer: defaultToConfig2,
    timeout: defaultToConfig2,
    timeoutMessage: defaultToConfig2,
    withCredentials: defaultToConfig2,
    withXSRFToken: defaultToConfig2,
    adapter: defaultToConfig2,
    responseType: defaultToConfig2,
    xsrfCookieName: defaultToConfig2,
    xsrfHeaderName: defaultToConfig2,
    onUploadProgress: defaultToConfig2,
    onDownloadProgress: defaultToConfig2,
    decompress: defaultToConfig2,
    maxContentLength: defaultToConfig2,
    maxBodyLength: defaultToConfig2,
    beforeRedirect: defaultToConfig2,
    transport: defaultToConfig2,
    httpAgent: defaultToConfig2,
    httpsAgent: defaultToConfig2,
    cancelToken: defaultToConfig2,
    socketPath: defaultToConfig2,
    allowedSocketPaths: defaultToConfig2,
    responseEncoding: defaultToConfig2,
    validateStatus: mergeDirectKeys,
    headers: (a, b, prop) => mergeDeepProperties(headersToObject(a), headersToObject(b), prop, true)
  };
  utils$1.forEach(Object.keys({ ...config1, ...config2 }), function computeConfigValue(prop) {
    if (prop === "__proto__" || prop === "constructor" || prop === "prototype") return;
    const merge2 = utils$1.hasOwnProp(mergeMap, prop) ? mergeMap[prop] : mergeDeepProperties;
    const a = utils$1.hasOwnProp(config1, prop) ? config1[prop] : void 0;
    const b = utils$1.hasOwnProp(config2, prop) ? config2[prop] : void 0;
    const configValue = merge2(a, b, prop);
    utils$1.isUndefined(configValue) && merge2 !== mergeDirectKeys || (config[prop] = configValue);
  });
  return config;
}
const FORM_DATA_CONTENT_HEADERS = ["content-type", "content-length"];
function setFormDataHeaders(headers, formHeaders, policy) {
  if (policy !== "content-only") {
    headers.set(formHeaders);
    return;
  }
  Object.entries(formHeaders).forEach(([key, val]) => {
    if (FORM_DATA_CONTENT_HEADERS.includes(key.toLowerCase())) {
      headers.set(key, val);
    }
  });
}
const encodeUTF8 = (str) => encodeURIComponent(str).replace(
  /%([0-9A-F]{2})/gi,
  (_, hex) => String.fromCharCode(parseInt(hex, 16))
);
const resolveConfig = (config) => {
  const newConfig = mergeConfig$1({}, config);
  const own2 = (key) => utils$1.hasOwnProp(newConfig, key) ? newConfig[key] : void 0;
  const data = own2("data");
  let withXSRFToken = own2("withXSRFToken");
  const xsrfHeaderName = own2("xsrfHeaderName");
  const xsrfCookieName = own2("xsrfCookieName");
  let headers = own2("headers");
  const auth = own2("auth");
  const baseURL = own2("baseURL");
  const allowAbsoluteUrls = own2("allowAbsoluteUrls");
  const url = own2("url");
  newConfig.headers = headers = AxiosHeaders$1.from(headers);
  newConfig.url = buildURL(
    buildFullPath(baseURL, url, allowAbsoluteUrls),
    config.params,
    config.paramsSerializer
  );
  if (auth) {
    headers.set(
      "Authorization",
      "Basic " + btoa((auth.username || "") + ":" + (auth.password ? encodeUTF8(auth.password) : ""))
    );
  }
  if (utils$1.isFormData(data)) {
    if (platform.hasStandardBrowserEnv || platform.hasStandardBrowserWebWorkerEnv) {
      headers.setContentType(void 0);
    } else if (utils$1.isFunction(data.getHeaders)) {
      setFormDataHeaders(headers, data.getHeaders(), own2("formDataHeaderPolicy"));
    }
  }
  if (platform.hasStandardBrowserEnv) {
    if (utils$1.isFunction(withXSRFToken)) {
      withXSRFToken = withXSRFToken(newConfig);
    }
    const shouldSendXSRF = withXSRFToken === true || withXSRFToken == null && isURLSameOrigin(newConfig.url);
    if (shouldSendXSRF) {
      const xsrfValue = xsrfHeaderName && xsrfCookieName && cookies.read(xsrfCookieName);
      if (xsrfValue) {
        headers.set(xsrfHeaderName, xsrfValue);
      }
    }
  }
  return newConfig;
};
const isXHRAdapterSupported = typeof XMLHttpRequest !== "undefined";
const xhrAdapter = isXHRAdapterSupported && function(config) {
  return new Promise(function dispatchXhrRequest(resolve2, reject) {
    const _config = resolveConfig(config);
    let requestData = _config.data;
    const requestHeaders = AxiosHeaders$1.from(_config.headers).normalize();
    let { responseType, onUploadProgress, onDownloadProgress } = _config;
    let onCanceled;
    let uploadThrottled, downloadThrottled;
    let flushUpload, flushDownload;
    function done() {
      flushUpload && flushUpload();
      flushDownload && flushDownload();
      _config.cancelToken && _config.cancelToken.unsubscribe(onCanceled);
      _config.signal && _config.signal.removeEventListener("abort", onCanceled);
    }
    let request = new XMLHttpRequest();
    request.open(_config.method.toUpperCase(), _config.url, true);
    request.timeout = _config.timeout;
    function onloadend() {
      if (!request) {
        return;
      }
      const responseHeaders = AxiosHeaders$1.from(
        "getAllResponseHeaders" in request && request.getAllResponseHeaders()
      );
      const responseData = !responseType || responseType === "text" || responseType === "json" ? request.responseText : request.response;
      const response = {
        data: responseData,
        status: request.status,
        statusText: request.statusText,
        headers: responseHeaders,
        config,
        request
      };
      settle(
        function _resolve(value) {
          resolve2(value);
          done();
        },
        function _reject(err) {
          reject(err);
          done();
        },
        response
      );
      request = null;
    }
    if ("onloadend" in request) {
      request.onloadend = onloadend;
    } else {
      request.onreadystatechange = function handleLoad() {
        if (!request || request.readyState !== 4) {
          return;
        }
        if (request.status === 0 && !(request.responseURL && request.responseURL.startsWith("file:"))) {
          return;
        }
        setTimeout(onloadend);
      };
    }
    request.onabort = function handleAbort() {
      if (!request) {
        return;
      }
      reject(new AxiosError$1("Request aborted", AxiosError$1.ECONNABORTED, config, request));
      done();
      request = null;
    };
    request.onerror = function handleError(event) {
      const msg = event && event.message ? event.message : "Network Error";
      const err = new AxiosError$1(msg, AxiosError$1.ERR_NETWORK, config, request);
      err.event = event || null;
      reject(err);
      done();
      request = null;
    };
    request.ontimeout = function handleTimeout() {
      let timeoutErrorMessage = _config.timeout ? "timeout of " + _config.timeout + "ms exceeded" : "timeout exceeded";
      const transitional2 = _config.transitional || transitionalDefaults;
      if (_config.timeoutErrorMessage) {
        timeoutErrorMessage = _config.timeoutErrorMessage;
      }
      reject(
        new AxiosError$1(
          timeoutErrorMessage,
          transitional2.clarifyTimeoutError ? AxiosError$1.ETIMEDOUT : AxiosError$1.ECONNABORTED,
          config,
          request
        )
      );
      done();
      request = null;
    };
    requestData === void 0 && requestHeaders.setContentType(null);
    if ("setRequestHeader" in request) {
      utils$1.forEach(requestHeaders.toJSON(), function setRequestHeader(val, key) {
        request.setRequestHeader(key, val);
      });
    }
    if (!utils$1.isUndefined(_config.withCredentials)) {
      request.withCredentials = !!_config.withCredentials;
    }
    if (responseType && responseType !== "json") {
      request.responseType = _config.responseType;
    }
    if (onDownloadProgress) {
      [downloadThrottled, flushDownload] = progressEventReducer(onDownloadProgress, true);
      request.addEventListener("progress", downloadThrottled);
    }
    if (onUploadProgress && request.upload) {
      [uploadThrottled, flushUpload] = progressEventReducer(onUploadProgress);
      request.upload.addEventListener("progress", uploadThrottled);
      request.upload.addEventListener("loadend", flushUpload);
    }
    if (_config.cancelToken || _config.signal) {
      onCanceled = (cancel) => {
        if (!request) {
          return;
        }
        reject(!cancel || cancel.type ? new CanceledError$1(null, config, request) : cancel);
        request.abort();
        done();
        request = null;
      };
      _config.cancelToken && _config.cancelToken.subscribe(onCanceled);
      if (_config.signal) {
        _config.signal.aborted ? onCanceled() : _config.signal.addEventListener("abort", onCanceled);
      }
    }
    const protocol = parseProtocol(_config.url);
    if (protocol && !platform.protocols.includes(protocol)) {
      reject(
        new AxiosError$1(
          "Unsupported protocol " + protocol + ":",
          AxiosError$1.ERR_BAD_REQUEST,
          config
        )
      );
      return;
    }
    request.send(requestData || null);
  });
};
const composeSignals = (signals, timeout) => {
  const { length } = signals = signals ? signals.filter(Boolean) : [];
  if (timeout || length) {
    let controller = new AbortController();
    let aborted;
    const onabort = function(reason) {
      if (!aborted) {
        aborted = true;
        unsubscribe();
        const err = reason instanceof Error ? reason : this.reason;
        controller.abort(
          err instanceof AxiosError$1 ? err : new CanceledError$1(err instanceof Error ? err.message : err)
        );
      }
    };
    let timer = timeout && setTimeout(() => {
      timer = null;
      onabort(new AxiosError$1(`timeout of ${timeout}ms exceeded`, AxiosError$1.ETIMEDOUT));
    }, timeout);
    const unsubscribe = () => {
      if (signals) {
        timer && clearTimeout(timer);
        timer = null;
        signals.forEach((signal2) => {
          signal2.unsubscribe ? signal2.unsubscribe(onabort) : signal2.removeEventListener("abort", onabort);
        });
        signals = null;
      }
    };
    signals.forEach((signal2) => signal2.addEventListener("abort", onabort));
    const { signal } = controller;
    signal.unsubscribe = () => utils$1.asap(unsubscribe);
    return signal;
  }
};
const streamChunk = function* (chunk, chunkSize) {
  let len = chunk.byteLength;
  if (len < chunkSize) {
    yield chunk;
    return;
  }
  let pos = 0;
  let end;
  while (pos < len) {
    end = pos + chunkSize;
    yield chunk.slice(pos, end);
    pos = end;
  }
};
const readBytes = async function* (iterable, chunkSize) {
  for await (const chunk of readStream(iterable)) {
    yield* streamChunk(chunk, chunkSize);
  }
};
const readStream = async function* (stream2) {
  if (stream2[Symbol.asyncIterator]) {
    yield* stream2;
    return;
  }
  const reader = stream2.getReader();
  try {
    for (; ; ) {
      const { done, value } = await reader.read();
      if (done) {
        break;
      }
      yield value;
    }
  } finally {
    await reader.cancel();
  }
};
const trackStream = (stream2, chunkSize, onProgress, onFinish) => {
  const iterator2 = readBytes(stream2, chunkSize);
  let bytes = 0;
  let done;
  let _onFinish = (e) => {
    if (!done) {
      done = true;
      onFinish && onFinish(e);
    }
  };
  return new ReadableStream(
    {
      async pull(controller) {
        try {
          const { done: done2, value } = await iterator2.next();
          if (done2) {
            _onFinish();
            controller.close();
            return;
          }
          let len = value.byteLength;
          if (onProgress) {
            let loadedBytes = bytes += len;
            onProgress(loadedBytes);
          }
          controller.enqueue(new Uint8Array(value));
        } catch (err) {
          _onFinish(err);
          throw err;
        }
      },
      cancel(reason) {
        _onFinish(reason);
        return iterator2.return();
      }
    },
    {
      highWaterMark: 2
    }
  );
};
const DEFAULT_CHUNK_SIZE = 64 * 1024;
const { isFunction } = utils$1;
const test = (fn, ...args) => {
  try {
    return !!fn(...args);
  } catch (e) {
    return false;
  }
};
const factory = (env) => {
  const globalObject = utils$1.global ?? globalThis;
  const { ReadableStream: ReadableStream2, TextEncoder: TextEncoder2 } = globalObject;
  env = utils$1.merge.call(
    {
      skipUndefined: true
    },
    {
      Request: globalObject.Request,
      Response: globalObject.Response
    },
    env
  );
  const { fetch: envFetch, Request, Response } = env;
  const isFetchSupported = envFetch ? isFunction(envFetch) : typeof fetch === "function";
  const isRequestSupported = isFunction(Request);
  const isResponseSupported = isFunction(Response);
  if (!isFetchSupported) {
    return false;
  }
  const isReadableStreamSupported = isFetchSupported && isFunction(ReadableStream2);
  const encodeText = isFetchSupported && (typeof TextEncoder2 === "function" ? /* @__PURE__ */ ((encoder) => (str) => encoder.encode(str))(new TextEncoder2()) : async (str) => new Uint8Array(await new Request(str).arrayBuffer()));
  const supportsRequestStream = isRequestSupported && isReadableStreamSupported && test(() => {
    let duplexAccessed = false;
    const request = new Request(platform.origin, {
      body: new ReadableStream2(),
      method: "POST",
      get duplex() {
        duplexAccessed = true;
        return "half";
      }
    });
    const hasContentType = request.headers.has("Content-Type");
    if (request.body != null) {
      request.body.cancel();
    }
    return duplexAccessed && !hasContentType;
  });
  const supportsResponseStream = isResponseSupported && isReadableStreamSupported && test(() => utils$1.isReadableStream(new Response("").body));
  const resolvers = {
    stream: supportsResponseStream && ((res) => res.body)
  };
  isFetchSupported && (() => {
    ["text", "arrayBuffer", "blob", "formData", "stream"].forEach((type2) => {
      !resolvers[type2] && (resolvers[type2] = (res, config) => {
        let method = res && res[type2];
        if (method) {
          return method.call(res);
        }
        throw new AxiosError$1(
          `Response type '${type2}' is not supported`,
          AxiosError$1.ERR_NOT_SUPPORT,
          config
        );
      });
    });
  })();
  const getBodyLength = async (body) => {
    if (body == null) {
      return 0;
    }
    if (utils$1.isBlob(body)) {
      return body.size;
    }
    if (utils$1.isSpecCompliantForm(body)) {
      const _request = new Request(platform.origin, {
        method: "POST",
        body
      });
      return (await _request.arrayBuffer()).byteLength;
    }
    if (utils$1.isArrayBufferView(body) || utils$1.isArrayBuffer(body)) {
      return body.byteLength;
    }
    if (utils$1.isURLSearchParams(body)) {
      body = body + "";
    }
    if (utils$1.isString(body)) {
      return (await encodeText(body)).byteLength;
    }
  };
  const resolveBodyLength = async (headers, body) => {
    const length = utils$1.toFiniteNumber(headers.getContentLength());
    return length == null ? getBodyLength(body) : length;
  };
  return async (config) => {
    let {
      url,
      method,
      data,
      signal,
      cancelToken,
      timeout,
      onDownloadProgress,
      onUploadProgress,
      responseType,
      headers,
      withCredentials = "same-origin",
      fetchOptions,
      maxContentLength,
      maxBodyLength
    } = resolveConfig(config);
    const hasMaxContentLength = utils$1.isNumber(maxContentLength) && maxContentLength > -1;
    const hasMaxBodyLength = utils$1.isNumber(maxBodyLength) && maxBodyLength > -1;
    let _fetch = envFetch || fetch;
    responseType = responseType ? (responseType + "").toLowerCase() : "text";
    let composedSignal = composeSignals(
      [signal, cancelToken && cancelToken.toAbortSignal()],
      timeout
    );
    let request = null;
    const unsubscribe = composedSignal && composedSignal.unsubscribe && (() => {
      composedSignal.unsubscribe();
    });
    let requestContentLength;
    try {
      if (hasMaxContentLength && typeof url === "string" && url.startsWith("data:")) {
        const estimated = estimateDataURLDecodedBytes(url);
        if (estimated > maxContentLength) {
          throw new AxiosError$1(
            "maxContentLength size of " + maxContentLength + " exceeded",
            AxiosError$1.ERR_BAD_RESPONSE,
            config,
            request
          );
        }
      }
      if (hasMaxBodyLength && method !== "get" && method !== "head") {
        const outboundLength = await resolveBodyLength(headers, data);
        if (typeof outboundLength === "number" && isFinite(outboundLength) && outboundLength > maxBodyLength) {
          throw new AxiosError$1(
            "Request body larger than maxBodyLength limit",
            AxiosError$1.ERR_BAD_REQUEST,
            config,
            request
          );
        }
      }
      if (onUploadProgress && supportsRequestStream && method !== "get" && method !== "head" && (requestContentLength = await resolveBodyLength(headers, data)) !== 0) {
        let _request = new Request(url, {
          method: "POST",
          body: data,
          duplex: "half"
        });
        let contentTypeHeader;
        if (utils$1.isFormData(data) && (contentTypeHeader = _request.headers.get("content-type"))) {
          headers.setContentType(contentTypeHeader);
        }
        if (_request.body) {
          const [onProgress, flush] = progressEventDecorator(
            requestContentLength,
            progressEventReducer(asyncDecorator(onUploadProgress))
          );
          data = trackStream(_request.body, DEFAULT_CHUNK_SIZE, onProgress, flush);
        }
      }
      if (!utils$1.isString(withCredentials)) {
        withCredentials = withCredentials ? "include" : "omit";
      }
      const isCredentialsSupported = isRequestSupported && "credentials" in Request.prototype;
      if (utils$1.isFormData(data)) {
        const contentType = headers.getContentType();
        if (contentType && /^multipart\/form-data/i.test(contentType) && !/boundary=/i.test(contentType)) {
          headers.delete("content-type");
        }
      }
      headers.set("User-Agent", "axios/" + VERSION$1, false);
      const resolvedOptions = {
        ...fetchOptions,
        signal: composedSignal,
        method: method.toUpperCase(),
        headers: headers.normalize().toJSON(),
        body: data,
        duplex: "half",
        credentials: isCredentialsSupported ? withCredentials : void 0
      };
      request = isRequestSupported && new Request(url, resolvedOptions);
      let response = await (isRequestSupported ? _fetch(request, fetchOptions) : _fetch(url, resolvedOptions));
      if (hasMaxContentLength) {
        const declaredLength = utils$1.toFiniteNumber(response.headers.get("content-length"));
        if (declaredLength != null && declaredLength > maxContentLength) {
          throw new AxiosError$1(
            "maxContentLength size of " + maxContentLength + " exceeded",
            AxiosError$1.ERR_BAD_RESPONSE,
            config,
            request
          );
        }
      }
      const isStreamResponse = supportsResponseStream && (responseType === "stream" || responseType === "response");
      if (supportsResponseStream && response.body && (onDownloadProgress || hasMaxContentLength || isStreamResponse && unsubscribe)) {
        const options = {};
        ["status", "statusText", "headers"].forEach((prop) => {
          options[prop] = response[prop];
        });
        const responseContentLength = utils$1.toFiniteNumber(response.headers.get("content-length"));
        const [onProgress, flush] = onDownloadProgress && progressEventDecorator(
          responseContentLength,
          progressEventReducer(asyncDecorator(onDownloadProgress), true)
        ) || [];
        let bytesRead = 0;
        const onChunkProgress = (loadedBytes) => {
          if (hasMaxContentLength) {
            bytesRead = loadedBytes;
            if (bytesRead > maxContentLength) {
              throw new AxiosError$1(
                "maxContentLength size of " + maxContentLength + " exceeded",
                AxiosError$1.ERR_BAD_RESPONSE,
                config,
                request
              );
            }
          }
          onProgress && onProgress(loadedBytes);
        };
        response = new Response(
          trackStream(response.body, DEFAULT_CHUNK_SIZE, onChunkProgress, () => {
            flush && flush();
            unsubscribe && unsubscribe();
          }),
          options
        );
      }
      responseType = responseType || "text";
      let responseData = await resolvers[utils$1.findKey(resolvers, responseType) || "text"](
        response,
        config
      );
      if (hasMaxContentLength && !supportsResponseStream && !isStreamResponse) {
        let materializedSize;
        if (responseData != null) {
          if (typeof responseData.byteLength === "number") {
            materializedSize = responseData.byteLength;
          } else if (typeof responseData.size === "number") {
            materializedSize = responseData.size;
          } else if (typeof responseData === "string") {
            materializedSize = typeof TextEncoder2 === "function" ? new TextEncoder2().encode(responseData).byteLength : responseData.length;
          }
        }
        if (typeof materializedSize === "number" && materializedSize > maxContentLength) {
          throw new AxiosError$1(
            "maxContentLength size of " + maxContentLength + " exceeded",
            AxiosError$1.ERR_BAD_RESPONSE,
            config,
            request
          );
        }
      }
      !isStreamResponse && unsubscribe && unsubscribe();
      return await new Promise((resolve2, reject) => {
        settle(resolve2, reject, {
          data: responseData,
          headers: AxiosHeaders$1.from(response.headers),
          status: response.status,
          statusText: response.statusText,
          config,
          request
        });
      });
    } catch (err) {
      unsubscribe && unsubscribe();
      if (composedSignal && composedSignal.aborted && composedSignal.reason instanceof AxiosError$1) {
        const canceledError = composedSignal.reason;
        canceledError.config = config;
        request && (canceledError.request = request);
        err !== canceledError && (canceledError.cause = err);
        throw canceledError;
      }
      if (err && err.name === "TypeError" && /Load failed|fetch/i.test(err.message)) {
        throw Object.assign(
          new AxiosError$1(
            "Network Error",
            AxiosError$1.ERR_NETWORK,
            config,
            request,
            err && err.response
          ),
          {
            cause: err.cause || err
          }
        );
      }
      throw AxiosError$1.from(err, err && err.code, config, request, err && err.response);
    }
  };
};
const seedCache = /* @__PURE__ */ new Map();
const getFetch = (config) => {
  let env = config && config.env || {};
  const { fetch: fetch2, Request, Response } = env;
  const seeds = [Request, Response, fetch2];
  let len = seeds.length, i = len, seed, target, map = seedCache;
  while (i--) {
    seed = seeds[i];
    target = map.get(seed);
    target === void 0 && map.set(seed, target = i ? /* @__PURE__ */ new Map() : factory(env));
    map = target;
  }
  return target;
};
getFetch();
const knownAdapters = {
  http: httpAdapter,
  xhr: xhrAdapter,
  fetch: {
    get: getFetch
  }
};
utils$1.forEach(knownAdapters, (fn, value) => {
  if (fn) {
    try {
      Object.defineProperty(fn, "name", { __proto__: null, value });
    } catch (e) {
    }
    Object.defineProperty(fn, "adapterName", { __proto__: null, value });
  }
});
const renderReason = (reason) => `- ${reason}`;
const isResolvedHandle = (adapter) => utils$1.isFunction(adapter) || adapter === null || adapter === false;
function getAdapter$1(adapters2, config) {
  adapters2 = utils$1.isArray(adapters2) ? adapters2 : [adapters2];
  const { length } = adapters2;
  let nameOrAdapter;
  let adapter;
  const rejectedReasons = {};
  for (let i = 0; i < length; i++) {
    nameOrAdapter = adapters2[i];
    let id;
    adapter = nameOrAdapter;
    if (!isResolvedHandle(nameOrAdapter)) {
      adapter = knownAdapters[(id = String(nameOrAdapter)).toLowerCase()];
      if (adapter === void 0) {
        throw new AxiosError$1(`Unknown adapter '${id}'`);
      }
    }
    if (adapter && (utils$1.isFunction(adapter) || (adapter = adapter.get(config)))) {
      break;
    }
    rejectedReasons[id || "#" + i] = adapter;
  }
  if (!adapter) {
    const reasons = Object.entries(rejectedReasons).map(
      ([id, state]) => `adapter ${id} ` + (state === false ? "is not supported by the environment" : "is not available in the build")
    );
    let s = length ? reasons.length > 1 ? "since :\n" + reasons.map(renderReason).join("\n") : " " + renderReason(reasons[0]) : "as no adapter specified";
    throw new AxiosError$1(
      `There is no suitable adapter to dispatch the request ` + s,
      "ERR_NOT_SUPPORT"
    );
  }
  return adapter;
}
const adapters = {
  /**
   * Resolve an adapter from a list of adapter names or functions.
   * @type {Function}
   */
  getAdapter: getAdapter$1,
  /**
   * Exposes all known adapters
   * @type {Object<string, Function|Object>}
   */
  adapters: knownAdapters
};
function throwIfCancellationRequested(config) {
  if (config.cancelToken) {
    config.cancelToken.throwIfRequested();
  }
  if (config.signal && config.signal.aborted) {
    throw new CanceledError$1(null, config);
  }
}
function dispatchRequest(config) {
  throwIfCancellationRequested(config);
  config.headers = AxiosHeaders$1.from(config.headers);
  config.data = transformData.call(config, config.transformRequest);
  if (["post", "put", "patch"].indexOf(config.method) !== -1) {
    config.headers.setContentType("application/x-www-form-urlencoded", false);
  }
  const adapter = adapters.getAdapter(config.adapter || defaults.adapter, config);
  return adapter(config).then(
    function onAdapterResolution(response) {
      throwIfCancellationRequested(config);
      config.response = response;
      try {
        response.data = transformData.call(config, config.transformResponse, response);
      } finally {
        delete config.response;
      }
      response.headers = AxiosHeaders$1.from(response.headers);
      return response;
    },
    function onAdapterRejection(reason) {
      if (!isCancel$1(reason)) {
        throwIfCancellationRequested(config);
        if (reason && reason.response) {
          config.response = reason.response;
          try {
            reason.response.data = transformData.call(
              config,
              config.transformResponse,
              reason.response
            );
          } finally {
            delete config.response;
          }
          reason.response.headers = AxiosHeaders$1.from(reason.response.headers);
        }
      }
      return Promise.reject(reason);
    }
  );
}
const validators$1 = {};
["object", "boolean", "number", "function", "string", "symbol"].forEach((type2, i) => {
  validators$1[type2] = function validator2(thing) {
    return typeof thing === type2 || "a" + (i < 1 ? "n " : " ") + type2;
  };
});
const deprecatedWarnings = {};
validators$1.transitional = function transitional(validator2, version, message) {
  function formatMessage(opt, desc) {
    return "[Axios v" + VERSION$1 + "] Transitional option '" + opt + "'" + desc + (message ? ". " + message : "");
  }
  return (value, opt, opts) => {
    if (validator2 === false) {
      throw new AxiosError$1(
        formatMessage(opt, " has been removed" + (version ? " in " + version : "")),
        AxiosError$1.ERR_DEPRECATED
      );
    }
    if (version && !deprecatedWarnings[opt]) {
      deprecatedWarnings[opt] = true;
      console.warn(
        formatMessage(
          opt,
          " has been deprecated since v" + version + " and will be removed in the near future"
        )
      );
    }
    return validator2 ? validator2(value, opt, opts) : true;
  };
};
validators$1.spelling = function spelling(correctSpelling) {
  return (value, opt) => {
    console.warn(`${opt} is likely a misspelling of ${correctSpelling}`);
    return true;
  };
};
function assertOptions(options, schema, allowUnknown) {
  if (typeof options !== "object") {
    throw new AxiosError$1("options must be an object", AxiosError$1.ERR_BAD_OPTION_VALUE);
  }
  const keys = Object.keys(options);
  let i = keys.length;
  while (i-- > 0) {
    const opt = keys[i];
    const validator2 = Object.prototype.hasOwnProperty.call(schema, opt) ? schema[opt] : void 0;
    if (validator2) {
      const value = options[opt];
      const result = value === void 0 || validator2(value, opt, options);
      if (result !== true) {
        throw new AxiosError$1(
          "option " + opt + " must be " + result,
          AxiosError$1.ERR_BAD_OPTION_VALUE
        );
      }
      continue;
    }
    if (allowUnknown !== true) {
      throw new AxiosError$1("Unknown option " + opt, AxiosError$1.ERR_BAD_OPTION);
    }
  }
}
const validator = {
  assertOptions,
  validators: validators$1
};
const validators = validator.validators;
let Axios$1 = class Axios {
  constructor(instanceConfig) {
    this.defaults = instanceConfig || {};
    this.interceptors = {
      request: new InterceptorManager(),
      response: new InterceptorManager()
    };
  }
  /**
   * Dispatch a request
   *
   * @param {String|Object} configOrUrl The config specific for this request (merged with this.defaults)
   * @param {?Object} config
   *
   * @returns {Promise} The Promise to be fulfilled
   */
  async request(configOrUrl, config) {
    try {
      return await this._request(configOrUrl, config);
    } catch (err) {
      if (err instanceof Error) {
        let dummy = {};
        Error.captureStackTrace ? Error.captureStackTrace(dummy) : dummy = new Error();
        const stack = (() => {
          if (!dummy.stack) {
            return "";
          }
          const firstNewlineIndex = dummy.stack.indexOf("\n");
          return firstNewlineIndex === -1 ? "" : dummy.stack.slice(firstNewlineIndex + 1);
        })();
        try {
          if (!err.stack) {
            err.stack = stack;
          } else if (stack) {
            const firstNewlineIndex = stack.indexOf("\n");
            const secondNewlineIndex = firstNewlineIndex === -1 ? -1 : stack.indexOf("\n", firstNewlineIndex + 1);
            const stackWithoutTwoTopLines = secondNewlineIndex === -1 ? "" : stack.slice(secondNewlineIndex + 1);
            if (!String(err.stack).endsWith(stackWithoutTwoTopLines)) {
              err.stack += "\n" + stack;
            }
          }
        } catch (e) {
        }
      }
      throw err;
    }
  }
  _request(configOrUrl, config) {
    if (typeof configOrUrl === "string") {
      config = config || {};
      config.url = configOrUrl;
    } else {
      config = configOrUrl || {};
    }
    config = mergeConfig$1(this.defaults, config);
    const { transitional: transitional2, paramsSerializer, headers } = config;
    if (transitional2 !== void 0) {
      validator.assertOptions(
        transitional2,
        {
          silentJSONParsing: validators.transitional(validators.boolean),
          forcedJSONParsing: validators.transitional(validators.boolean),
          clarifyTimeoutError: validators.transitional(validators.boolean),
          legacyInterceptorReqResOrdering: validators.transitional(validators.boolean)
        },
        false
      );
    }
    if (paramsSerializer != null) {
      if (utils$1.isFunction(paramsSerializer)) {
        config.paramsSerializer = {
          serialize: paramsSerializer
        };
      } else {
        validator.assertOptions(
          paramsSerializer,
          {
            encode: validators.function,
            serialize: validators.function
          },
          true
        );
      }
    }
    if (config.allowAbsoluteUrls !== void 0) ;
    else if (this.defaults.allowAbsoluteUrls !== void 0) {
      config.allowAbsoluteUrls = this.defaults.allowAbsoluteUrls;
    } else {
      config.allowAbsoluteUrls = true;
    }
    validator.assertOptions(
      config,
      {
        baseUrl: validators.spelling("baseURL"),
        withXsrfToken: validators.spelling("withXSRFToken")
      },
      true
    );
    config.method = (config.method || this.defaults.method || "get").toLowerCase();
    let contextHeaders = headers && utils$1.merge(headers.common, headers[config.method]);
    headers && utils$1.forEach(["delete", "get", "head", "post", "put", "patch", "query", "common"], (method) => {
      delete headers[method];
    });
    config.headers = AxiosHeaders$1.concat(contextHeaders, headers);
    const requestInterceptorChain = [];
    let synchronousRequestInterceptors = true;
    this.interceptors.request.forEach(function unshiftRequestInterceptors(interceptor) {
      if (typeof interceptor.runWhen === "function" && interceptor.runWhen(config) === false) {
        return;
      }
      synchronousRequestInterceptors = synchronousRequestInterceptors && interceptor.synchronous;
      const transitional3 = config.transitional || transitionalDefaults;
      const legacyInterceptorReqResOrdering = transitional3 && transitional3.legacyInterceptorReqResOrdering;
      if (legacyInterceptorReqResOrdering) {
        requestInterceptorChain.unshift(interceptor.fulfilled, interceptor.rejected);
      } else {
        requestInterceptorChain.push(interceptor.fulfilled, interceptor.rejected);
      }
    });
    const responseInterceptorChain = [];
    this.interceptors.response.forEach(function pushResponseInterceptors(interceptor) {
      responseInterceptorChain.push(interceptor.fulfilled, interceptor.rejected);
    });
    let promise;
    let i = 0;
    let len;
    if (!synchronousRequestInterceptors) {
      const chain = [dispatchRequest.bind(this), void 0];
      chain.unshift(...requestInterceptorChain);
      chain.push(...responseInterceptorChain);
      len = chain.length;
      promise = Promise.resolve(config);
      while (i < len) {
        promise = promise.then(chain[i++], chain[i++]);
      }
      return promise;
    }
    len = requestInterceptorChain.length;
    let newConfig = config;
    while (i < len) {
      const onFulfilled = requestInterceptorChain[i++];
      const onRejected = requestInterceptorChain[i++];
      try {
        newConfig = onFulfilled(newConfig);
      } catch (error) {
        onRejected.call(this, error);
        break;
      }
    }
    try {
      promise = dispatchRequest.call(this, newConfig);
    } catch (error) {
      return Promise.reject(error);
    }
    i = 0;
    len = responseInterceptorChain.length;
    while (i < len) {
      promise = promise.then(responseInterceptorChain[i++], responseInterceptorChain[i++]);
    }
    return promise;
  }
  getUri(config) {
    config = mergeConfig$1(this.defaults, config);
    const fullPath = buildFullPath(config.baseURL, config.url, config.allowAbsoluteUrls);
    return buildURL(fullPath, config.params, config.paramsSerializer);
  }
};
utils$1.forEach(["delete", "get", "head", "options"], function forEachMethodNoData(method) {
  Axios$1.prototype[method] = function(url, config) {
    return this.request(
      mergeConfig$1(config || {}, {
        method,
        url,
        data: (config || {}).data
      })
    );
  };
});
utils$1.forEach(["post", "put", "patch", "query"], function forEachMethodWithData(method) {
  function generateHTTPMethod(isForm) {
    return function httpMethod(url, data, config) {
      return this.request(
        mergeConfig$1(config || {}, {
          method,
          headers: isForm ? {
            "Content-Type": "multipart/form-data"
          } : {},
          url,
          data
        })
      );
    };
  }
  Axios$1.prototype[method] = generateHTTPMethod();
  if (method !== "query") {
    Axios$1.prototype[method + "Form"] = generateHTTPMethod(true);
  }
});
let CancelToken$1 = class CancelToken {
  constructor(executor) {
    if (typeof executor !== "function") {
      throw new TypeError("executor must be a function.");
    }
    let resolvePromise;
    this.promise = new Promise(function promiseExecutor(resolve2) {
      resolvePromise = resolve2;
    });
    const token = this;
    this.promise.then((cancel) => {
      if (!token._listeners) return;
      let i = token._listeners.length;
      while (i-- > 0) {
        token._listeners[i](cancel);
      }
      token._listeners = null;
    });
    this.promise.then = (onfulfilled) => {
      let _resolve;
      const promise = new Promise((resolve2) => {
        token.subscribe(resolve2);
        _resolve = resolve2;
      }).then(onfulfilled);
      promise.cancel = function reject() {
        token.unsubscribe(_resolve);
      };
      return promise;
    };
    executor(function cancel(message, config, request) {
      if (token.reason) {
        return;
      }
      token.reason = new CanceledError$1(message, config, request);
      resolvePromise(token.reason);
    });
  }
  /**
   * Throws a `CanceledError` if cancellation has been requested.
   */
  throwIfRequested() {
    if (this.reason) {
      throw this.reason;
    }
  }
  /**
   * Subscribe to the cancel signal
   */
  subscribe(listener) {
    if (this.reason) {
      listener(this.reason);
      return;
    }
    if (this._listeners) {
      this._listeners.push(listener);
    } else {
      this._listeners = [listener];
    }
  }
  /**
   * Unsubscribe from the cancel signal
   */
  unsubscribe(listener) {
    if (!this._listeners) {
      return;
    }
    const index = this._listeners.indexOf(listener);
    if (index !== -1) {
      this._listeners.splice(index, 1);
    }
  }
  toAbortSignal() {
    const controller = new AbortController();
    const abort = (err) => {
      controller.abort(err);
    };
    this.subscribe(abort);
    controller.signal.unsubscribe = () => this.unsubscribe(abort);
    return controller.signal;
  }
  /**
   * Returns an object that contains a new `CancelToken` and a function that, when called,
   * cancels the `CancelToken`.
   */
  static source() {
    let cancel;
    const token = new CancelToken(function executor(c) {
      cancel = c;
    });
    return {
      token,
      cancel
    };
  }
};
function spread$1(callback) {
  return function wrap(arr) {
    return callback.apply(null, arr);
  };
}
function isAxiosError$1(payload) {
  return utils$1.isObject(payload) && payload.isAxiosError === true;
}
const HttpStatusCode$1 = {
  Continue: 100,
  SwitchingProtocols: 101,
  Processing: 102,
  EarlyHints: 103,
  Ok: 200,
  Created: 201,
  Accepted: 202,
  NonAuthoritativeInformation: 203,
  NoContent: 204,
  ResetContent: 205,
  PartialContent: 206,
  MultiStatus: 207,
  AlreadyReported: 208,
  ImUsed: 226,
  MultipleChoices: 300,
  MovedPermanently: 301,
  Found: 302,
  SeeOther: 303,
  NotModified: 304,
  UseProxy: 305,
  Unused: 306,
  TemporaryRedirect: 307,
  PermanentRedirect: 308,
  BadRequest: 400,
  Unauthorized: 401,
  PaymentRequired: 402,
  Forbidden: 403,
  NotFound: 404,
  MethodNotAllowed: 405,
  NotAcceptable: 406,
  ProxyAuthenticationRequired: 407,
  RequestTimeout: 408,
  Conflict: 409,
  Gone: 410,
  LengthRequired: 411,
  PreconditionFailed: 412,
  PayloadTooLarge: 413,
  UriTooLong: 414,
  UnsupportedMediaType: 415,
  RangeNotSatisfiable: 416,
  ExpectationFailed: 417,
  ImATeapot: 418,
  MisdirectedRequest: 421,
  UnprocessableEntity: 422,
  Locked: 423,
  FailedDependency: 424,
  TooEarly: 425,
  UpgradeRequired: 426,
  PreconditionRequired: 428,
  TooManyRequests: 429,
  RequestHeaderFieldsTooLarge: 431,
  UnavailableForLegalReasons: 451,
  InternalServerError: 500,
  NotImplemented: 501,
  BadGateway: 502,
  ServiceUnavailable: 503,
  GatewayTimeout: 504,
  HttpVersionNotSupported: 505,
  VariantAlsoNegotiates: 506,
  InsufficientStorage: 507,
  LoopDetected: 508,
  NotExtended: 510,
  NetworkAuthenticationRequired: 511,
  WebServerIsDown: 521,
  ConnectionTimedOut: 522,
  OriginIsUnreachable: 523,
  TimeoutOccurred: 524,
  SslHandshakeFailed: 525,
  InvalidSslCertificate: 526
};
Object.entries(HttpStatusCode$1).forEach(([key, value]) => {
  HttpStatusCode$1[value] = key;
});
function createInstance(defaultConfig) {
  const context = new Axios$1(defaultConfig);
  const instance = bind(Axios$1.prototype.request, context);
  utils$1.extend(instance, Axios$1.prototype, context, { allOwnKeys: true });
  utils$1.extend(instance, context, null, { allOwnKeys: true });
  instance.create = function create2(instanceConfig) {
    return createInstance(mergeConfig$1(defaultConfig, instanceConfig));
  };
  return instance;
}
const axios = createInstance(defaults);
axios.Axios = Axios$1;
axios.CanceledError = CanceledError$1;
axios.CancelToken = CancelToken$1;
axios.isCancel = isCancel$1;
axios.VERSION = VERSION$1;
axios.toFormData = toFormData$1;
axios.AxiosError = AxiosError$1;
axios.Cancel = axios.CanceledError;
axios.all = function all(promises) {
  return Promise.all(promises);
};
axios.spread = spread$1;
axios.isAxiosError = isAxiosError$1;
axios.mergeConfig = mergeConfig$1;
axios.AxiosHeaders = AxiosHeaders$1;
axios.formToJSON = (thing) => formDataToJSON(utils$1.isHTMLForm(thing) ? new FormData(thing) : thing);
axios.getAdapter = adapters.getAdapter;
axios.HttpStatusCode = HttpStatusCode$1;
axios.default = axios;
const {
  Axios: Axios2,
  AxiosError: AxiosError2,
  CanceledError: CanceledError2,
  isCancel,
  CancelToken: CancelToken2,
  VERSION,
  all: all2,
  Cancel,
  isAxiosError,
  spread,
  toFormData,
  AxiosHeaders: AxiosHeaders2,
  HttpStatusCode,
  formToJSON,
  getAdapter,
  mergeConfig,
  create
} = axios;
const api = axios.create({
  baseURL: "/api/wali",
  withCredentials: true,
  headers: {
    "Accept": "application/json",
    "Content-Type": "application/json"
  }
});
const postLogin = (data) => api.post("/login", data);
const fetchDashboard = () => api.get("/dashboard");
const fetchStudents = () => api.get("/students");
const fetchActiveStudent = () => api.get("/active-student");
const fetchSaldoHistories = (params) => api.get("/saldo-histories", { params });
const fetchBills = () => api.get("/bills");
const fetchBillDetail = (id) => api.get(`/bills/${id}`);
const fetchPosTransactions = (params) => api.get("/pos-transactions", { params });
const postTopup = (data) => api.post("/topup", data);
const postCheckout = (data) => api.post("/checkout", data);
const fetchPaymentDetail = (id) => api.get(`/payment/${id}`);
const uploadPaymentProof = (id, formData) => api.post(`/payment/${id}/upload-proof`, formData, {
  headers: { "Content-Type": "multipart/form-data" }
});
const fetchLimit = () => api.get("/limit");
const updateLimit = (data) => api.put("/limit", data);
const fetchProfile = () => api.get("/profile");
const SantriContext = reactExports.createContext(null);
const mapSantri = (s) => ({
  ...s,
  initials: s.name ? s.name.split(" ").map((n) => n[0]).join("").substring(0, 2).toUpperCase() : "S",
  className: s.classroom?.name || "-",
  jenjang: s.school?.name || "-",
  cardSuffix: s.barcode ? s.barcode.slice(-4) : "****"
});
function SantriProvider({ children }) {
  const queryClient = useQueryClient();
  const { data: students = [], isLoading: isLoadingStudents } = useQuery({
    queryKey: ["students"],
    queryFn: async () => {
      const res = await fetchStudents();
      return (res.data || []).map(mapSantri);
    }
  });
  const { data: active = null, isLoading: isLoadingActive } = useQuery({
    queryKey: ["active-student"],
    queryFn: async () => {
      const res = await fetchActiveStudent();
      return res.data ? mapSantri(res.data) : null;
    }
  });
  const switchStudent = async (id) => {
    try {
      await axios.post(`/wali/switch-student/${id}`, {}, {
        withCredentials: true,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content
        }
      });
      queryClient.invalidateQueries({ queryKey: ["active-student"] });
      queryClient.invalidateQueries({ queryKey: ["dashboard"] });
      queryClient.invalidateQueries({ queryKey: ["bills"] });
      queryClient.invalidateQueries({ queryKey: ["saldo-histories"] });
    } catch (error) {
      console.error("Failed to switch student", error);
    }
  };
  return /* @__PURE__ */ jsxRuntimeExports.jsx(
    SantriContext.Provider,
    {
      value: {
        santri: students,
        active,
        isLoading: isLoadingStudents || isLoadingActive,
        switchStudent
      },
      children
    }
  );
}
function useSantri() {
  const ctx = reactExports.useContext(SantriContext);
  if (!ctx) throw new Error("useSantri must be used within SantriProvider");
  return ctx;
}
const mergeClasses = (...classes) => classes.filter((className, index, array) => {
  return Boolean(className) && className.trim() !== "" && array.indexOf(className) === index;
}).join(" ").trim();
const toKebabCase = (string) => string.replace(/([a-z0-9])([A-Z])/g, "$1-$2").toLowerCase();
const toCamelCase = (string) => string.replace(
  /^([A-Z])|[\s-_]+(\w)/g,
  (match, p1, p2) => p2 ? p2.toUpperCase() : p1.toLowerCase()
);
const toPascalCase = (string) => {
  const camelCase = toCamelCase(string);
  return camelCase.charAt(0).toUpperCase() + camelCase.slice(1);
};
var defaultAttributes = {
  xmlns: "http://www.w3.org/2000/svg",
  width: 24,
  height: 24,
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: 2,
  strokeLinecap: "round",
  strokeLinejoin: "round"
};
const hasA11yProp = (props) => {
  for (const prop in props) {
    if (prop.startsWith("aria-") || prop === "role" || prop === "title") {
      return true;
    }
  }
  return false;
};
const Icon = reactExports.forwardRef(
  ({
    color = "currentColor",
    size = 24,
    strokeWidth = 2,
    absoluteStrokeWidth,
    className = "",
    children,
    iconNode,
    ...rest
  }, ref2) => reactExports.createElement(
    "svg",
    {
      ref: ref2,
      ...defaultAttributes,
      width: size,
      height: size,
      stroke: color,
      strokeWidth: absoluteStrokeWidth ? Number(strokeWidth) * 24 / Number(size) : strokeWidth,
      className: mergeClasses("lucide", className),
      ...!children && !hasA11yProp(rest) && { "aria-hidden": "true" },
      ...rest
    },
    [
      ...iconNode.map(([tag, attrs]) => reactExports.createElement(tag, attrs)),
      ...Array.isArray(children) ? children : [children]
    ]
  )
);
const createLucideIcon = (iconName, iconNode) => {
  const Component = reactExports.forwardRef(
    ({ className, ...props }, ref2) => reactExports.createElement(Icon, {
      ref: ref2,
      iconNode,
      className: mergeClasses(
        `lucide-${toKebabCase(toPascalCase(iconName))}`,
        `lucide-${iconName}`,
        className
      ),
      ...props
    })
  );
  Component.displayName = toPascalCase(iconName);
  return Component;
};
const __iconNode$4 = [
  ["circle", { cx: "12", cy: "12", r: "10", key: "1mglay" }],
  ["path", { d: "m15 9-6 6", key: "1uzhvr" }],
  ["path", { d: "m9 9 6 6", key: "z0biqf" }]
];
const CircleX = createLucideIcon("circle-x", __iconNode$4);
const __iconNode$3 = [
  ["path", { d: "M12 15V3", key: "m9g1x1" }],
  ["path", { d: "M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4", key: "ih7n3h" }],
  ["path", { d: "m7 10 5 5 5-5", key: "brsn70" }]
];
const Download = createLucideIcon("download", __iconNode$3);
const __iconNode$2 = [
  ["path", { d: "M12 2v13", key: "1km8f5" }],
  ["path", { d: "m16 6-4-4-4 4", key: "13yo43" }],
  ["path", { d: "M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8", key: "1b2hhj" }]
];
const Share = createLucideIcon("share", __iconNode$2);
const __iconNode$1 = [
  ["rect", { width: "18", height: "18", x: "3", y: "3", rx: "2", key: "afitv7" }],
  ["path", { d: "M8 12h8", key: "1wcyev" }],
  ["path", { d: "M12 8v8", key: "napkw2" }]
];
const SquarePlus = createLucideIcon("square-plus", __iconNode$1);
const __iconNode = [
  ["path", { d: "M18 6 6 18", key: "1bl5f8" }],
  ["path", { d: "m6 6 12 12", key: "d8bk6v" }]
];
const X = createLucideIcon("x", __iconNode);
const InstallPromptModal = () => {
  const [deferredPrompt, setDeferredPrompt] = reactExports.useState(null);
  const [show, setShow] = reactExports.useState(false);
  reactExports.useEffect(() => {
    const handler = (e) => {
      e.preventDefault();
      setDeferredPrompt(e);
      setShow(true);
    };
    window.addEventListener("beforeinstallprompt", handler);
    return () => window.removeEventListener("beforeinstallprompt", handler);
  }, []);
  const install = async () => {
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;
    setShow(false);
    if (outcome === "accepted") {
      console.log("PWA installed");
    }
    setDeferredPrompt(null);
  };
  if (!show) return null;
  return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "fixed inset-0 z-[100] flex items-end justify-center sm:items-center p-4 bg-black/40 backdrop-blur-sm animate-in fade-in duration-300", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "bg-white rounded-[32px] shadow-2xl p-6 w-full max-w-sm animate-in slide-in-from-bottom-8 duration-500", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex justify-between items-start mb-4", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "bg-blue-50 p-3 rounded-2xl", children: /* @__PURE__ */ jsxRuntimeExports.jsx(Download, { className: "text-blue-600", size: 24 }) }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => setShow(false), className: "text-slate-400 hover:text-slate-600 transition-colors", children: /* @__PURE__ */ jsxRuntimeExports.jsx(CircleX, { size: 24 }) })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("h2", { className: "text-xl font-bold text-slate-900 mb-2", children: "Pasang Aplikasi" }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-slate-600 text-sm mb-6 leading-relaxed", children: "Tambahkan Portal Wali ke layar utama ponsel Anda untuk akses yang lebih cepat, stabil, dan hemat kuota." }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex flex-col gap-3", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx(
        "button",
        {
          onClick: install,
          className: "w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 rounded-2xl transition-all active:scale-95 shadow-lg shadow-blue-200",
          children: "Pasang Sekarang"
        }
      ),
      /* @__PURE__ */ jsxRuntimeExports.jsx(
        "button",
        {
          onClick: () => setShow(false),
          className: "w-full py-3 text-slate-400 text-sm font-medium hover:text-slate-600 transition-colors",
          children: "Nanti Saja"
        }
      )
    ] })
  ] }) });
};
const IOSInstallBanner = () => {
  const [show, setShow] = reactExports.useState(false);
  reactExports.useEffect(() => {
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const isStandalone = window.matchMedia("(display-mode: standalone)").matches;
    if (isIOS && !isStandalone) {
      const dismissed = sessionStorage.getItem("ios-prompt-dismissed");
      if (!dismissed) {
        setShow(true);
      }
    }
  }, []);
  const dismiss = () => {
    setShow(false);
    sessionStorage.setItem("ios-prompt-dismissed", "true");
  };
  if (!show) return null;
  return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "fixed bottom-6 left-4 right-4 z-[99] animate-in slide-in-from-bottom-full duration-700", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "bg-white/90 backdrop-blur-xl border border-white/20 rounded-[24px] shadow-premium p-4 relative overflow-hidden", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "absolute top-0 left-0 w-1 h-full bg-blue-600" }),
    /* @__PURE__ */ jsxRuntimeExports.jsx(
      "button",
      {
        onClick: dismiss,
        className: "absolute top-3 right-3 text-slate-400 p-1",
        children: /* @__PURE__ */ jsxRuntimeExports.jsx(X, { size: 16 })
      }
    ),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center gap-4 pr-6", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "flex-shrink-0 w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-100", children: /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-white font-bold text-xl", children: "W" }) }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-slate-900 font-bold text-sm leading-tight mb-1", children: "Pasang di iPhone Anda" }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-slate-500 text-[11px] leading-snug", children: [
          "Ketuk ",
          /* @__PURE__ */ jsxRuntimeExports.jsx(Share, { className: "inline-block mx-0.5", size: 12 }),
          " lalu pilih ",
          /* @__PURE__ */ jsxRuntimeExports.jsx(SquarePlus, { className: "inline-block mx-0.5", size: 12 }),
          " ",
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "font-semibold text-slate-700", children: "Tambah ke Layar Utama" })
        ] })
      ] })
    ] })
  ] }) });
};
function NotFoundComponent() {
  return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "flex min-h-screen items-center justify-center bg-background px-4", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "max-w-md text-center", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx("h1", { className: "text-7xl font-bold text-foreground", children: "404" }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("h2", { className: "mt-4 text-xl font-semibold text-foreground", children: "Page not found" }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "mt-2 text-sm text-muted-foreground", children: "The page you're looking for doesn't exist or has been moved." }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "mt-6", children: /* @__PURE__ */ jsxRuntimeExports.jsx(
      Link,
      {
        to: "/",
        className: "inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90",
        children: "Go home"
      }
    ) })
  ] }) });
}
function ErrorComponent({ error, reset }) {
  console.error(error);
  const router2 = useRouter();
  return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "flex min-h-screen items-center justify-center bg-background px-4", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "max-w-md text-center", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx("h1", { className: "text-xl font-semibold tracking-tight text-foreground", children: "This page didn't load" }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "mt-2 text-sm text-muted-foreground", children: "Something went wrong on our end. You can try refreshing or head back home." }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-6 flex flex-wrap justify-center gap-2", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx(
        "button",
        {
          onClick: () => {
            router2.invalidate();
            reset();
          },
          className: "inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90",
          children: "Try again"
        }
      ),
      /* @__PURE__ */ jsxRuntimeExports.jsx(
        "a",
        {
          href: "/",
          className: "inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent",
          children: "Go home"
        }
      )
    ] })
  ] }) });
}
const Route$a = createRootRouteWithContext()({
  head: () => ({
    meta: [
      { charSet: "utf-8" },
      { name: "viewport", content: "width=device-width, initial-scale=1" },
      { title: "Lovable App" },
      { name: "description", content: "A modern fintech mobile app for parents and students to manage school finances and transactions." },
      { name: "author", content: "Lovable" },
      { property: "og:title", content: "Lovable App" },
      { property: "og:description", content: "A modern fintech mobile app for parents and students to manage school finances and transactions." },
      { property: "og:type", content: "website" },
      { name: "twitter:card", content: "summary" },
      { name: "twitter:site", content: "@Lovable" },
      { name: "twitter:title", content: "Lovable App" },
      { name: "twitter:description", content: "A modern fintech mobile app for parents and students to manage school finances and transactions." },
      { property: "og:image", content: "https://pub-bb2e103a32db4e198524a2e9ed8f35b4.r2.dev/36d114ed-6400-4cbb-8be2-b7481563b603/id-preview-29c3cd1a--480d78d1-3b0b-4b54-b47c-1928e52dec53.lovable.app-1778423056931.png" },
      { name: "twitter:image", content: "https://pub-bb2e103a32db4e198524a2e9ed8f35b4.r2.dev/36d114ed-6400-4cbb-8be2-b7481563b603/id-preview-29c3cd1a--480d78d1-3b0b-4b54-b47c-1928e52dec53.lovable.app-1778423056931.png" }
    ],
    links: [
      {
        rel: "stylesheet",
        href: appCss
      }
    ]
  }),
  shellComponent: RootShell,
  component: RootComponent,
  notFoundComponent: NotFoundComponent,
  errorComponent: ErrorComponent
});
function RootShell({ children }) {
  return /* @__PURE__ */ jsxRuntimeExports.jsxs("html", { lang: "en", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx("head", { children: /* @__PURE__ */ jsxRuntimeExports.jsx(HeadContent, {}) }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("body", { children: [
      children,
      /* @__PURE__ */ jsxRuntimeExports.jsx(Scripts, {})
    ] })
  ] });
}
function RootComponent() {
  const { queryClient } = Route$a.useRouteContext();
  return /* @__PURE__ */ jsxRuntimeExports.jsx(QueryClientProvider, { client: queryClient, children: /* @__PURE__ */ jsxRuntimeExports.jsxs(SantriProvider, { children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx(Outlet, {}),
    /* @__PURE__ */ jsxRuntimeExports.jsx(InstallPromptModal, {}),
    /* @__PURE__ */ jsxRuntimeExports.jsx(IOSInstallBanner, {})
  ] }) });
}
const $$splitComponentImporter$9 = () => import("./topup-DpAPmZFm.js");
const Route$9 = createFileRoute("/topup")({
  component: lazyRouteComponent($$splitComponentImporter$9, "component"),
  head: () => ({
    meta: [{
      title: "Topup Saldo — SantriPay"
    }]
  })
});
const $$splitComponentImporter$8 = () => import("./tagihan-CExC7kVb.js");
const Route$8 = createFileRoute("/tagihan")({
  component: lazyRouteComponent($$splitComponentImporter$8, "component"),
  head: () => ({
    meta: [{
      title: "Tagihan — SantriPay"
    }]
  })
});
const $$splitComponentImporter$7 = () => import("./riwayat-HRpPExvN.js");
const Route$7 = createFileRoute("/riwayat")({
  component: lazyRouteComponent($$splitComponentImporter$7, "component"),
  head: () => ({
    meta: [{
      title: "Riwayat Transaksi — SantriPay"
    }]
  })
});
const $$splitComponentImporter$6 = () => import("./profil-DXn-b91E.js");
const Route$6 = createFileRoute("/profil")({
  component: lazyRouteComponent($$splitComponentImporter$6, "component"),
  head: () => ({
    meta: [{
      title: "Profil — SantriPay"
    }]
  })
});
const $$splitComponentImporter$5 = () => import("./limit-Cc3odziD.js");
const Route$5 = createFileRoute("/limit")({
  component: lazyRouteComponent($$splitComponentImporter$5, "component"),
  head: () => ({
    meta: [{
      title: "Atur Limit — SantriPay"
    }]
  })
});
const $$splitComponentImporter$4 = () => import("./dashboard-DbqKBMw4.js");
const Route$4 = createFileRoute("/dashboard")({
  component: lazyRouteComponent($$splitComponentImporter$4, "component"),
  head: () => ({
    meta: [{
      title: "Beranda — SantriPay"
    }]
  })
});
const $$splitComponentImporter$3 = () => import("./index-C4M6qwk8.js");
const Route$3 = createFileRoute("/")({
  component: lazyRouteComponent($$splitComponentImporter$3, "component"),
  head: () => ({
    meta: [{
      title: "Masuk — SantriPay"
    }, {
      name: "description",
      content: "Aplikasi keuangan digital untuk wali santri dan siswa."
    }]
  })
});
const $$splitComponentImporter$2 = () => import("./tagihan_._billId-BRLD67iJ.js");
const Route$2 = createFileRoute("/tagihan_/$billId")({
  component: lazyRouteComponent($$splitComponentImporter$2, "component"),
  head: () => ({
    meta: [{
      title: `Detail Tagihan — SantriPay`
    }]
  })
});
const $$splitComponentImporter$1 = () => import("./pembayaran._payId-DXHxN9D0.js");
const Route$1 = createFileRoute("/pembayaran/$payId")({
  component: lazyRouteComponent($$splitComponentImporter$1, "component"),
  head: () => ({
    meta: [{
      title: "Pembayaran — SantriPay"
    }]
  })
});
const $$splitComponentImporter = () => import("./admin.approval-BOO6ItUP.js");
const Route2 = createFileRoute("/admin/approval")({
  component: lazyRouteComponent($$splitComponentImporter, "component"),
  head: () => ({
    meta: [{
      title: "Approval Pembayaran — Admin"
    }]
  })
});
const TopupRoute = Route$9.update({
  id: "/topup",
  path: "/topup",
  getParentRoute: () => Route$a
});
const TagihanRoute = Route$8.update({
  id: "/tagihan",
  path: "/tagihan",
  getParentRoute: () => Route$a
});
const RiwayatRoute = Route$7.update({
  id: "/riwayat",
  path: "/riwayat",
  getParentRoute: () => Route$a
});
const ProfilRoute = Route$6.update({
  id: "/profil",
  path: "/profil",
  getParentRoute: () => Route$a
});
const LimitRoute = Route$5.update({
  id: "/limit",
  path: "/limit",
  getParentRoute: () => Route$a
});
const DashboardRoute = Route$4.update({
  id: "/dashboard",
  path: "/dashboard",
  getParentRoute: () => Route$a
});
const IndexRoute = Route$3.update({
  id: "/",
  path: "/",
  getParentRoute: () => Route$a
});
const TagihanBillIdRoute = Route$2.update({
  id: "/tagihan_/$billId",
  path: "/tagihan/$billId",
  getParentRoute: () => Route$a
});
const PembayaranPayIdRoute = Route$1.update({
  id: "/pembayaran/$payId",
  path: "/pembayaran/$payId",
  getParentRoute: () => Route$a
});
const AdminApprovalRoute = Route2.update({
  id: "/admin/approval",
  path: "/admin/approval",
  getParentRoute: () => Route$a
});
const rootRouteChildren = {
  IndexRoute,
  DashboardRoute,
  LimitRoute,
  ProfilRoute,
  RiwayatRoute,
  TagihanRoute,
  TopupRoute,
  AdminApprovalRoute,
  PembayaranPayIdRoute,
  TagihanBillIdRoute
};
const routeTree = Route$a._addFileChildren(rootRouteChildren)._addFileTypes();
const getRouter = () => {
  const queryClient = new QueryClient();
  const router2 = createRouter({
    routeTree,
    context: { queryClient },
    scrollRestoration: true,
    defaultPreloadStaleTime: 0
  });
  return router2;
};
const router = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  getRouter
}, Symbol.toStringTag, { value: "Module" }));
export {
  CircleX as C,
  Link as L,
  Subscribable as S,
  X,
  useSantri as a,
  useQueryClient as b,
  createLucideIcon as c,
  uploadPaymentProof as d,
  useQuery as e,
  fetchBills as f,
  fetchSaldoHistories as g,
  fetchPosTransactions as h,
  fetchProfile as i,
  fetchLimit as j,
  updateLimit as k,
  fetchDashboard as l,
  postLogin as m,
  useParams as n,
  fetchBillDetail as o,
  postTopup as p,
  postCheckout as q,
  fetchPaymentDetail as r,
  shallowEqualObjects as s,
  hashKey as t,
  useNavigate as u,
  getDefaultState as v,
  notifyManager as w,
  noop$1 as x,
  shouldThrowError as y,
  router as z
};
